"""Test Customer/Broker/Isolation role policy."""
import sys, os
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
import re
os.environ["CHATBOT_LLM_ENHANCE"] = "false"
from app_bds import BDSChatbot
bot = BDSChatbot()

def s(html):
    return re.sub(r"<[^>]+>", "", html or "")[:200]

def chat(sid, msg, role="guest"):
    r = bot.process({"message": msg, "session_id": sid, "role": role})
    resp = r.get("response","")
    print(f"  [{role}] {msg}")
    print(f"  Bot: {s(resp)}")
    print(f"  Quick: {r.get('quick_replies',[])}")
    print()
    return r

def ok(r): return not any(m in r.get("response","") for m in ["kết nối chưa","sự cố","EXCEPTION"])

print("="*60)
print("Test Customer 1: Gói tin phải bị chặn")
print("="*60)
r = chat("c1","Gói tin đăng BĐS giá bao nhiêu?","khach-hang")
if "Gói Cơ Bản" in r.get("response","") and "Để mua gói và đăng bài" in r.get("response",""):
    print("  === PASS: Trả bảng giá và có text chặn mua ===\n")
else:
    print(f"  !!! FAIL: Không đúng format, response: {s(r.get('response',''))[:100]}\n")

print("="*60)
print("Test Customer 2: Full booking flow")
print("="*60)
chat("c2","có căn hộ nào 4 tỷ không","khach-hang")
chat("c2","căn 1","khach-hang")
chat("c2","đặt lịch","khach-hang")
chat("c2","Tối mai","khach-hang")
r2 = chat("c2","19h","khach-hang")
if ok(r2) and "19:00" in r2.get("response",""):
    print("  === PASS: Booking OK ===\n")
else:
    print(f"  !!! FAIL: {s(r2.get('response',''))[:100]}\n")

print("="*60)
print("Test Customer 3: Hướng dẫn đăng bài bị chặn")
print("="*60)
r3 = chat("c3","Hướng dẫn đăng bài","khach-hang")
if "môi giới" in r3.get("response","").lower():
    print("  === PASS: Redirect đúng ===\n")
else:
    print(f"  !!! FAIL: {s(r3.get('response',''))[:100]}\n")

print("="*60)
print("Test Broker 1: Package flow đúng")
print("="*60)
chat("b1","Gói tin đăng BĐS giá bao nhiêu?","moi-gioi")
chat("b1","Cách mua gói tin","moi-gioi")
chat("b1","Hướng dẫn đăng bài","moi-gioi")
r_b1 = chat("b1","Xem lại gói tin","moi-gioi")
if "gói" in r_b1.get("response","").lower() or "đ/7 ngày" in r_b1.get("response",""):
    print("  === PASS: Package flow broker OK ===\n")
else:
    print(f"  !!! CHECK b1 xem lai: {s(r_b1.get('response',''))[:100]}\n")

print("="*60)
print("Test Broker 2: Search BĐS bị chặn")
print("="*60)
r_b2 = chat("b2","có căn hộ nào 4 tỷ không","moi-gioi")
if "khách hàng" in r_b2.get("response","").lower() or "môi giới" in r_b2.get("response","").lower():
    print("  === PASS: Search bị chặn ===\n")
else:
    print(f"  !!! FAIL: {s(r_b2.get('response',''))[:100]}\n")

print("="*60)
print("Test Broker 3: Đặt lịch xem nhà → xem lịch hẹn")
print("="*60)
r_b3 = chat("b3","Lịch khách hẹn xem nhà","moi-gioi")
if "lịch hẹn" in r_b3.get("response","").lower() or "dashboard" in r_b3.get("response","").lower():
    print("  === PASS: Broker appointment redirect ===\n")
else:
    print(f"  !!! CHECK b3: {s(r_b3.get('response',''))[:100]}\n")

print("="*60)
print("Test Isolation: cùng user_id, 2 actor khác nhau")
print("="*60)
chat("customer:user99","có căn hộ nào 4 tỷ không","khach-hang")
chat("customer:user99","căn 1","khach-hang")
chat("broker:user99","Gói tin đăng BĐS giá bao nhiêu?","moi-gioi")
r_iso = chat("customer:user99","đặt lịch","khach-hang")
if ok(r_iso) and ("Căn hộ" in r_iso.get("response","") or "xem" in r_iso.get("response","").lower()):
    print("  === PASS: Customer vẫn nhớ căn 1 ===\n")
else:
    print(f"  !!! CHECK isolation: {s(r_iso.get('response',''))[:100]}\n")

print("="*60)
print("Test Customer 4: Cách trở thành môi giới")
print("="*60)
r4 = chat("c4", "Làm sao trở thành môi giới", "khach-hang")
if "1. Đăng xuất" in r4.get("response","") and "link /moi-gioi/dang-ky" in r4.get("response",""):
    print("  === PASS: Trả đúng flow ===\n")
else:
    print(f"  !!! FAIL: {s(r4.get('response',''))[:100]}\n")

print("="*60)
print("Test Customer 5: Tìm BĐS khoảng 4 tỷ")
print("="*60)
r5 = chat("c5", "Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ", "khach-hang")
if "Chung cư cao cấp Thanh Khê" in r5.get("response",""):
    print("  === PASS: Tìm thấy chung cư 3 tỷ ===\n")
else:
    print(f"  !!! FAIL: Không thấy chung cư 3 tỷ. Response: {s(r5.get('response',''))[:200]}\n")

print("="*60)
print("Test Customer A: Valuation Base")
print("="*60)
ra = chat("ca", "Định giá BĐS", "khach-hang")
print("  === CHECK: Valuation Base ===\n")

print("="*60)
print("Test Customer B: Valuation detail")
print("="*60)
rb = chat("ca", "Căn hộ Hải Châu 60m²", "khach-hang")
print("  === CHECK: Valuation detail ===\n")

print("="*60)
print("Test Customer C: Cùng khu vực")
print("="*60)
rc1 = chat("cc", "Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ", "khach-hang")
rc2 = chat("cc", "căn 2", "khach-hang")
rc3 = chat("cc", "Tìm BĐS cùng khu vực", "khach-hang")
print("  === CHECK: Cùng khu vực ===\n")

print("="*60)
print("Test Customer D: Xem tất cả")
print("="*60)
rd = chat("cd", "xem tất cả bất động sản", "khach-hang")
print("  === CHECK: Xem tất cả ===\n")

print("="*60)
print("Test Customer E: Rẻ hơn")
print("="*60)
re1 = chat("ce", "Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ", "khach-hang")
re2 = chat("ce", "Rẻ hơn", "khach-hang")
print("  === CHECK: Rẻ hơn ===\n")

print("="*60)
print("Test Customer F: Rộng hơn")
print("="*60)
rf1 = chat("cf", "Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ", "khach-hang")
rf2 = chat("cf", "Rộng hơn", "khach-hang")
print("  === CHECK: Rộng hơn ===\n")

print("="*60)
print("Test Customer G: Gần biển")
print("="*60)
rg1 = chat("cg", "Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ", "khach-hang")
rg2 = chat("cg", "Gần biển", "khach-hang")
print("  === CHECK: Gần biển ===\n")

print("="*60)
print("Test Customer H: Fallback")
print("="*60)
rh = chat("ch", "abcxyz", "khach-hang")
print("  === CHECK: Fallback ===\n")
