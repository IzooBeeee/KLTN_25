"""
Test Global Intent Override: booking context must be escapable.
Scenarios:
  1. Active booking → "Gói VIP bao nhiêu tin"   => override to package_info, NO date question
  2. Active booking → "Tìm đất nền Sơn Trà"     => override to search_property, reset booking
  3. Active booking → "hủy"                       => clear booking, reply "Đã hủy đặt lịch"
  4. Active booking → "Tối mai lúc 19h"           => CONTINUE booking (not overridden)
  5. Active booking → "Định giá nhà"              => override to valuation
"""
import sys, os
sys.path.insert(0, os.path.dirname(__file__))

import unittest.mock as mock
from unittest.mock import MagicMock, patch

# Silence logging for cleaner output
import logging
logging.basicConfig(level=logging.WARNING)

sys.modules['google'] = MagicMock()
sys.modules['google.generativeai'] = MagicMock()
sys.modules['groq'] = MagicMock()

FAKE_ROWS = [{
    "id": 10, "tieu_de": "Căn hộ Test", "mo_ta": "test", "gia": 3_000_000_000,
    "dien_tich": 60.0, "so_phong_ngu": 2, "so_phong_tam": 1, "is_noi_bat": 1,
    "created_at": "2025-01-01", "ten_loai": "căn hộ",
    "tinh": "Đà Nẵng", "quan": "Sơn Trà", "phuong": "An Hải Bắc",
    "dia_chi_chi_tiet": "123 Võ Nguyên Giáp", "moi_gioi": "MG Test",
    "anh_dai_dien_url": None,
}]
FAKE_PACKAGES = [{
    "id": 1, "ten_goi": "Gói VIP 30", "mo_ta": "Gói VIP 30 tin", "gia": 990_000,
    "so_ngay": 30, "so_luong_tin": 10, "gan_nhan_vip": 1, "uu_tien_hien_thi": 1,
}]

with patch('mysql.connector.connect') as mock_conn:
    mock_cur = MagicMock()
    mock_cur.fetchall.return_value = FAKE_ROWS
    mock_conn.return_value.cursor.return_value = mock_cur

    from app_bds import BDSChatbot, new_booking_state

    chatbot = BDSChatbot()
    chatbot.gemini.enabled = False
    chatbot.gemini.groq_client = None

    def assert_(cond, msg=""):
        if not cond:
            raise AssertionError(msg)

    PASS = []
    FAIL = []

    def run_scenario(name, session, turns):
        """
        turns = list of (message, assert_fn)
        assert_fn(result, mem) -> None, raises AssertionError on failure
        """
        for msg, assert_fn in turns:
            mock_cur.fetchall.return_value = FAKE_ROWS
            result = chatbot.process({"message": msg, "session_id": session,
                                       "user_context": {"is_logged_in": True}})
            mem = chatbot.memory.get(session)
            try:
                assert_fn(result, mem)
            except AssertionError as e:
                FAIL.append(f"FAIL [{name}] msg={msg!r}: {e}")
                return
        PASS.append(f"PASS [{name}]")

    # ── Scenario 1: Package override ─────────────────────────────────────────
    S1 = "test_pkg_override"
    # Seed an active booking
    mem1 = chatbot.memory.get(S1)
    mem1.booking_state = {"status": "collecting", "property_id": 10,
                           "property_title": "Căn hộ Test", "date": None, "time": None}
    mem1.selected_property = FAKE_ROWS[0]

    mock_cur.fetchall.return_value = FAKE_PACKAGES
    run_scenario("Gói VIP trong booking", S1, [
        ("Gói VIP bao nhiêu tin", lambda r, m: (
            # booking state must be cleared
            assert_(not m.booking_state, f"booking_state not cleared: {m.booking_state}"),
            # must NOT ask about date
            assert_("xem ngày nào" not in (r.get("response") or "").lower()
                    and "mấy giờ" not in (r.get("response") or "").lower(),
                    f"Still asking date/time: {r.get('response','')[:100]}"),
        )),
    ])

    # ── Scenario 2: Search override ───────────────────────────────────────────
    S2 = "test_search_override"
    mem2 = chatbot.memory.get(S2)
    mem2.booking_state = {"status": "collecting", "property_id": 10,
                           "property_title": "Căn hộ Test", "date": None, "time": None}
    mem2.selected_property = FAKE_ROWS[0]
    mock_cur.fetchall.return_value = FAKE_ROWS

    run_scenario("Tìm đất nền trong booking", S2, [
        ("Tìm đất nền Sơn Trà 2 tỷ", lambda r, m: (
            assert_(not m.booking_state, f"booking_state not cleared: {m.booking_state}"),
            assert_("xem ngày nào" not in (r.get("response") or "").lower()
                    and "mấy giờ" not in (r.get("response") or "").lower(),
                    f"Still asking date/time: {r.get('response','')[:100]}"),
        )),
    ])

    # ── Scenario 3: Cancel / hủy ──────────────────────────────────────────────
    S3 = "test_cancel"
    mem3 = chatbot.memory.get(S3)
    mem3.booking_state = {"status": "collecting", "property_id": 10,
                           "property_title": "Căn hộ Test", "date": None, "time": None}
    mem3.selected_property = FAKE_ROWS[0]

    run_scenario("Hủy booking", S3, [
        ("hủy", lambda r, m: (
            assert_(not m.booking_state, f"booking_state not cleared: {m.booking_state}"),
            assert_("đã hủy đặt lịch" in (r.get("response") or "").lower(),
                    f"Expected 'đã hủy đặt lịch', got: {r.get('response','')[:100]}"),
        )),
    ])

    # ── Scenario 4: Date/time MUST continue booking ───────────────────────────
    S4 = "test_continue_booking"
    mem4 = chatbot.memory.get(S4)
    mem4.booking_state = {"status": "collecting", "property_id": 10,
                           "property_title": "Căn hộ Test", "date": None, "time": None}
    mem4.selected_property = FAKE_ROWS[0]
    mem4.active_context = "booking"          # ← seed: user is in booking context
    mem4.active_domain = "property"
    mem4.active_task = "appointment"         # ← seed: NLU OOD guard will not block

    run_scenario("Tiếp tục booking với ngày giờ", S4, [
        ("Tối mai lúc 19h30", lambda r, m: (
            assert_(r.get("intent") == "appointment",
                    f"Expected appointment intent, got {r.get('intent')}"),
        )),
    ])

    # ── Scenario 5: Valuation override ───────────────────────────────────────
    S5 = "test_valuation_override"
    mem5 = chatbot.memory.get(S5)
    mem5.booking_state = {"status": "collecting", "property_id": 10,
                           "property_title": "Căn hộ Test", "date": None, "time": None}
    mem5.selected_property = FAKE_ROWS[0]

    run_scenario("Định giá trong booking", S5, [
        ("Định giá nhà mình muốn biết", lambda r, m: (
            assert_(not m.booking_state, f"booking_state not cleared: {m.booking_state}"),
        )),
    ])

    # ── Print results ─────────────────────────────────────────────────────────
    print()
    for p in PASS:
        print(f"  ✅ {p}")
    for f in FAIL:
        print(f"  ❌ {f}")
    print()
    if FAIL:
        print(f"RESULT: {len(FAIL)} FAILED / {len(PASS)+len(FAIL)} total")
        sys.exit(1)
    else:
        print(f"RESULT: ALL {len(PASS)} SCENARIOS PASSED ✅")
