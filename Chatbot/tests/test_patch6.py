import sys
sys.path.insert(0, '.')
sys.stdout.reconfigure(encoding='utf-8')
from app_bds import BDSChatbot

bot = BDSChatbot()

def p(label, r):
    reply = r.get('response', '')[:150]
    qr = r.get('quick_replies', [])
    print(f"[{label}]")
    print(f"  reply: {reply}")
    print(f"  quick: {qr}")
    print()

print("="*60)
print("TEST 1: Tìm căn hộ 4 tỷ -> Gần biển -> Mở rộng Sơn Trà")
bot.process({'message': 'Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ', 'session_id': 't1', 'actor': 'customer'})
bot.process({'message': 'Gần biển', 'session_id': 't1', 'actor': 'customer'})
r = bot.process({'message': 'Mở rộng sang Sơn Trà/Ngũ Hành Sơn', 'session_id': 't1', 'actor': 'customer'})
p("T1", r)

print("="*60)
print("TEST 2: Xem căn hộ phù hợp nhất")
bot.process({'message': 'Nhà phố Hải Châu 10 tỷ', 'session_id': 't2', 'actor': 'customer'}) # Search house
r = bot.process({'message': 'Xem căn hộ phù hợp nhất', 'session_id': 't2', 'actor': 'customer'})
p("T2", r)

print("="*60)
print("TEST 3: Xem tất cả BĐS gần biển")
r = bot.process({'message': 'Xem tất cả BĐS gần biển', 'session_id': 't3', 'actor': 'customer'})
p("T3", r)

print("="*60)
print("TEST 4: Đổi sang nhà phố gần biển")
bot.process({'message': 'Căn hộ Hải Châu 5 tỷ', 'session_id': 't4', 'actor': 'customer'})
r = bot.process({'message': 'Đổi sang nhà phố gần biển', 'session_id': 't4', 'actor': 'customer'})
p("T4", r)

print("="*60)
print("TEST 5: Tìm BĐS -> Khoảng 4 tỷ")
bot.process({'message': 'Tìm BĐS', 'session_id': 't5', 'actor': 'customer'})
r = bot.process({'message': 'Khoảng 4 tỷ', 'session_id': 't5', 'actor': 'customer'})
p("T5", r)

print("="*60)
print("TEST 6: Định giá -> Đặt lịch")
bot.process({'message': 'Định giá BĐS', 'session_id': 't6', 'actor': 'customer'})
bot.process({'message': 'Đất nền Liên Chiểu 150m²', 'session_id': 't6', 'actor': 'customer'})
r = bot.process({'message': 'Đặt lịch xem nhà', 'session_id': 't6', 'actor': 'customer'})
p("T6", r)

print("="*60)
print("TEST 7: Booking regression")
bot.process({'message': 'Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ', 'session_id': 't7', 'actor': 'customer'})
bot.process({'message': 'căn 1', 'session_id': 't7', 'actor': 'customer'})
bot.process({'message': 'đặt lịch', 'session_id': 't7', 'actor': 'customer'})
bot.process({'message': 'tối mai', 'session_id': 't7', 'actor': 'customer'})
r = bot.process({'message': '19h', 'session_id': 't7', 'actor': 'customer'})
p("T7", r)