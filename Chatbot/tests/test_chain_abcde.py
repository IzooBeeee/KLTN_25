"""Test A/B/C/D/E theo yêu cầu."""
import os, re, traceback
os.environ["CHATBOT_LLM_ENHANCE"] = "false"
from app_bds import BDSChatbot
bot = BDSChatbot()

def s(html):
    return re.sub(r"<[^>]+>", "", html or "")[:220]

def chat(sid, msg):
    try:
        r = bot.process({"message": msg, "session_id": sid})
        resp = r.get("response", "")
        print(f"  User: {msg}")
        print(f"  Bot:  {s(resp)}")
        print(f"  Quick:{r.get('quick_replies', [])}")
        print()
        return r
    except Exception as e:
        print(f"  !!! EXCEPTION: {e}")
        traceback.print_exc()
        return {"response": "EXCEPTION", "quick_replies": []}

FAIL_MARKERS = ["kết nối chưa", "sự cố", "ổn định", "EXCEPTION"]
def ok(r):
    txt = r.get("response", "")
    return not any(m in txt for m in FAIL_MARKERS)

print("=" * 60)
print("Test A: Full no-result chain + Studio")
print("=" * 60)
SID = "ta"
chat(SID, "có căn hộ nào 4 tỷ không")
chat(SID, "tìm căn hộ Đà Nẵng khoảng 2 tỷ")
chat(SID, "Nới ngân sách 20%")
chat(SID, "Tìm khu vực gần đó")
chat(SID, "Đổi loại BĐS tương tự")
r_studio = chat(SID, "Studio")
if ok(r_studio) and "lọc theo giá" not in r_studio.get("response",""):
    print("  === Test A (Studio) PASS ===\n")
else:
    print("  !!! Test A (Studio) FAIL\n")

print("=" * 60)
print("Test B: Sau chain, quay lại search mới + chọn căn")
print("=" * 60)
r_b1 = chat(SID, "có căn hộ nào 4 tỷ không")
r_b2 = chat(SID, "căn 1")
if ok(r_b1) and ok(r_b2) and "Căn hộ" in r_b2.get("response",""):
    print("  === Test B PASS ===\n")
else:
    print(f"  !!! Test B FAIL | b1={ok(r_b1)} b2={ok(r_b2)}\n")

print("=" * 60)
print("Test C: 5 lần search sau chain")
print("=" * 60)
all_ok = True
for i in range(5):
    r = chat(f"tc{i}", "có căn hộ nào 4 tỷ không")
    if not ok(r):
        print(f"  !!! lần {i+1} FAIL")
        all_ok = False
if all_ok:
    print("  === Test C PASS (5/5) ===\n")

print("=" * 60)
print("Test D: Chọn căn 1 và căn 2 sau search")
print("=" * 60)
chat("td", "có căn hộ nào 4 tỷ không")
r_d1 = chat("td", "căn 1")
r_d2 = chat("td", "căn 2")
if ok(r_d1) and ok(r_d2):
    print("  === Test D PASS ===\n")
else:
    print(f"  !!! Test D FAIL | d1={ok(r_d1)} d2={ok(r_d2)}\n")

print("=" * 60)
print("Test E: Full regression")
print("=" * 60)
chat("te", "Gói tin đăng BĐS giá bao nhiêu?")
chat("te", "Cách mua gói tin")
chat("te", "Hướng dẫn đăng bài")
chat("te", "Xem lại gói tin")
chat("te", "có căn hộ nào 4 tỷ không")
chat("te", "căn 1")
chat("te", "đặt lịch")
chat("te", "Tối mai")
r_e = chat("te", "19h")
if ok(r_e) and ("19:00" in r_e.get("response","") or "lịch xem" in r_e.get("response","").lower() or "đang gửi" in r_e.get("response","").lower()):
    print("  === Test E PASS ===\n")
else:
    print(f"  !!! Test E FAIL | response={s(r_e.get('response',''))}\n")
