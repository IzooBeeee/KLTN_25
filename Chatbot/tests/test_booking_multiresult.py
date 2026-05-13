import os, re
os.environ["CHATBOT_LLM_ENHANCE"] = "false"
from app_bds import BDSChatbot
bot = BDSChatbot()

def s(html):
    return re.sub(r"<[^>]+>", "", html or "")[:200]

def chat(sid, msg):
    r = bot.process({"message": msg, "session_id": sid})
    print(f"  User: {msg}")
    print(f"  Bot:  {s(r.get('response',''))}")
    print(f"  Quick:{r.get('quick_replies', [])}")
    print()
    return r

print("=== Test 1: Đặt lịch xem nhà từ danh sách 2 căn (phải hỏi căn nào) ===")
chat("t1", "có căn hộ nào 4 tỷ không")
r = chat("t1", "Đặt lịch xem nhà")
if "căn nào" in r.get("response","") or "căn 1" in r.get("response","").lower():
    print("  === PASS: Hỏi chọn căn ===\n")
else:
    print("  !!! FAIL: Không hỏi chọn căn\n")

print("=== Test 2: Tiếp theo chọn Căn 1 → hỏi ngày ===")
r2 = chat("t1", "Căn 1")
if "ngày" in r2.get("response","") or "Tối mai" in str(r2.get("quick_replies",[])):
    print("  === PASS: Hỏi ngày ===\n")
else:
    print("  !!! FAIL:", s(r2.get("response",""))[:80])

print("=== Test 3: 'đặt lịch' sau khi đã chọn căn (phải dùng selected_property) ===")
chat("t3", "có căn hộ nào 4 tỷ không")
chat("t3", "căn 2")
r3 = chat("t3", "đặt lịch")
if "Chung cư" in r3.get("response","") or "Thanh Khê" in r3.get("response","") or "ngày" in r3.get("response",""):
    print("  === PASS: Dùng selected_property ===\n")
else:
    print("  !!! FAIL:", s(r3.get("response",""))[:80])

print("=== Test 4: 'Đặt lịch căn đó' sau chọn căn → hỏi ngày luôn ===")
chat("t4", "có căn hộ nào 4 tỷ không")
chat("t4", "căn 1")
r4 = chat("t4", "Đặt lịch căn đó")
if "ngày" in r4.get("response",""):
    print("  === PASS ===\n")
else:
    print("  !!! FAIL:", s(r4.get("response",""))[:80])

print("=== Test 5: Full booking after asking property ===")
chat("t5", "có căn hộ nào 4 tỷ không")
chat("t5", "Đặt lịch xem nhà")
chat("t5", "Căn 2")
chat("t5", "Tối mai")
r5 = chat("t5", "19h")
if "19:00" in r5.get("response","") or "đang gửi" in r5.get("response","").lower():
    print("  === PASS: Booking hoàn tất ===\n")
else:
    print("  !!! FAIL:", s(r5.get("response",""))[:100])

print("=== Test 6: Single result — không hỏi, dùng luôn ===")
# Giả lập chỉ 1 kết quả bằng cách search hẹp
chat("t6", "tìm căn hộ Hải Châu 3 tỷ rưỡi")
r6 = chat("t6", "Đặt lịch xem nhà")
print("  Quick:", r6.get("quick_replies",[]))
# Nếu 1 kết quả hoặc 0 kết quả thì được hỏi ngày hoặc hỏi căn, không có "căn nào"
