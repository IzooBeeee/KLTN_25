import os, re
os.environ["CHATBOT_LLM_ENHANCE"] = "false"
from app_bds import BDSChatbot
bot = BDSChatbot()

def s(html):
    return re.sub(r"<[^>]+>", "", html or "")[:250]

def chat(sid, msg):
    r = bot.process({"message": msg, "session_id": sid})
    print(f"  User: {msg}")
    print(f"  Bot:  {s(r.get('response',''))}")
    print(f"  Quick:{r.get('quick_replies', [])}")
    print()
    return r

print("=" * 60)
print("Test 1: Stability - 5 lần cùng câu")
print("=" * 60)
FAIL = False
for i in range(5):
    r = chat(f"t1s{i}", "có căn hộ nào 4 tỷ không")
    if "kết nối" in r.get("response","") or "sự cố" in r.get("response",""):
        print(f"  !!! FAIL lần {i+1}")
        FAIL = True
if not FAIL:
    print("  === PASS: Không lần nào bị lỗi kết nối ===\n")

print("=" * 60)
print("Test 2: No result -> Nới ngân sách 20%")
print("=" * 60)
chat("t2", "tìm căn hộ Đà Nẵng khoảng 2 tỷ")
r2 = chat("t2", "Nới ngân sách 20%")
if "lọc theo giá" in r2.get("response",""):
    print("  !!! FAIL: Bot hỏi lại thay vì tự nới ngân sách")
else:
    print("  === PASS ===\n")

print("=" * 60)
print("Test 3: No result -> Tìm khu vực gần đó")
print("=" * 60)
chat("t3", "tìm căn hộ Hải Châu khoảng 2 tỷ")
r3 = chat("t3", "Tìm khu vực gần đó")
if "lọc theo giá" in r3.get("response",""):
    print("  !!! FAIL: Bot hỏi chung chung")
else:
    print("  === PASS ===\n")

print("=" * 60)
print("Test 4: Search -> package -> back search (Rẻ hơn)")
print("=" * 60)
chat("t4", "có căn hộ nào 4 tỷ không")
chat("t4", "Cách mua gói tin")
r4 = chat("t4", "Rẻ hơn")
if "căn hộ" in r4.get("response","").lower() or "tỷ" in r4.get("response","").lower():
    print("  === PASS: Rẻ hơn vẫn refine ===\n")
else:
    print("  !!! CHECK: Rẻ hơn ->", s(r4.get("response",""))[:80], "\n")

print("=" * 60)
print("Test 5: Property detail -> Tìm căn tương tự")
print("=" * 60)
chat("t5", "có căn hộ nào 4 tỷ không")
chat("t5", "căn 1")
r5 = chat("t5", "Tìm căn tương tự")
if "lọc theo giá" in r5.get("response",""):
    print("  !!! FAIL: Bot hỏi chung chung")
else:
    print("  === PASS ===\n")

print("=" * 60)
print("Test 6: Full regression")
print("=" * 60)
chat("t6", "Gói tin đăng BĐS giá bao nhiêu?")
chat("t6", "Cách mua gói tin")
chat("t6", "Hướng dẫn đăng bài")
chat("t6", "Xem lại gói tin")
chat("t6", "có căn hộ nào 4 tỷ không")
chat("t6", "căn 1")
chat("t6", "đặt lịch")
chat("t6", "Tối mai")
r6 = chat("t6", "19h")
if "chốt" in r6.get("response","").lower() or "đang gửi" in r6.get("response","").lower() or "lịch xem" in r6.get("response","").lower():
    print("  === PASS: Booking chốt thành công ===")
else:
    print("  !!! CHECK BOOKING END:", s(r6.get("response",""))[:100])
