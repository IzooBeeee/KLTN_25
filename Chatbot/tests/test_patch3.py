import sys
sys.path.insert(0, '.')
sys.stdout.reconfigure(encoding='utf-8')
from app_bds import BDSChatbot

bot = BDSChatbot()

def p(label, r):
    reply = r.get('response', '')[:120]
    qr = r.get('quick_replies', [])
    print(f"[{label}]")
    print(f"  reply: {reply}")
    print(f"  quick: {qr}")
    print()

print("="*60)
print("TEST 1: Gợi ý nhà gần biển, view đẹp (Rule-based detection)")
r = bot.process({'message': 'Gợi ý nhà gần biển, view đẹp', 'session_id': 't1', 'actor': 'customer'})
p("T1", r)

print("="*60)
print("TEST 2: Định giá (Diacritics) → Căn hộ Hải Châu 60m²")
bot.process({'message': 'Định giá BĐS', 'session_id': 't2', 'actor': 'customer'})
r = bot.process({'message': 'Căn hộ Hải Châu 60m²', 'session_id': 't2', 'actor': 'customer'})
p("T2", r)

print("="*60)
print("TEST 3: Dinh gia (ASCII) → Nha pho Son Tra 100m²")
bot.process({'message': 'Dinh gia BDS', 'session_id': 't3', 'actor': 'customer'})
r = bot.process({'message': 'Nhà phố Sơn Trà 100m²', 'session_id': 't3', 'actor': 'customer'})
p("T3", r)

print("="*60)
print("TEST 4: Định giá → Đất nền Liên Chiểu (Partial info)")
bot.process({'message': 'Định giá BĐS', 'session_id': 't4', 'actor': 'customer'})
r = bot.process({'message': 'Đất nền Liên Chiểu', 'session_id': 't4', 'actor': 'customer'})
p("T4", r)

print("="*60)
print("TEST 5: T4 continuation → 150m² (Context merge)")
r = bot.process({'message': '150m²', 'session_id': 't4', 'actor': 'customer'})
p("T5", r)

print("="*60)
print("TEST 6: Gần biển (Standalone, no search history)")
r = bot.process({'message': 'Gần biển', 'session_id': 't6', 'actor': 'customer'})
p("T6", r)

print("="*60)
print("TEST 7: Booking chain regression")
bot.process({'message': 'Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ', 'session_id': 't7', 'actor': 'customer'})
bot.process({'message': 'căn 1', 'session_id': 't7', 'actor': 'customer'})
bot.process({'message': 'đặt lịch', 'session_id': 't7', 'actor': 'customer'})
bot.process({'message': 'tối mai', 'session_id': 't7', 'actor': 'customer'})
r = bot.process({'message': '19h', 'session_id': 't7', 'actor': 'customer'})
p("T7", r)