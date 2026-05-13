import sys
sys.path.insert(0, '.')
from app_bds import BDSChatbot
import json

bot = BDSChatbot()

def s(res):
    return res.get('response', '')

print('='*60)
print('Test 1: Gần biển -> no result -> quick replies')
print('='*60)
r1 = bot.process({'message': 'Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ', 'session_id': 'u1', 'actor': 'customer'})
print('Search:', s(r1)[:100])
r2 = bot.process({'message': 'Gần biển', 'session_id': 'u1', 'actor': 'customer'})
print('Gần biển:', s(r2))
print('Quick:', r2.get('quick_replies'))

print('='*60)
print('Test 2: Nới ngân sách 20% -> no result -> exit')
print('='*60)
r3 = bot.process({'message': 'Nới ngân sách 20%', 'session_id': 'u1', 'actor': 'customer'})
print('Nới ngân sách:', s(r3))
print('Quick:', r3.get('quick_replies'))

print('='*60)
print('Test 3: Tìm khu vực gần đó -> no result -> combined exit')
print('='*60)
r4 = bot.process({'message': 'Tìm khu vực gần đó', 'session_id': 'u1', 'actor': 'customer'})
print('Tìm khu vực:', s(r4))
print('Quick:', r4.get('quick_replies'))

print('='*60)
print('Test 4: Xem căn hộ phù hợp nhất -> escape successful')
print('='*60)
r5 = bot.process({'message': 'Xem căn hộ phù hợp nhất', 'session_id': 'u1', 'actor': 'customer'})
print('Xem căn hộ:', s(r5)[:100])
print('Quick:', r5.get('quick_replies'))

print('='*60)
print('Test 5: Đổi loại BĐS -> Studio -> exit')
print('='*60)
bot.process({'message': 'Tôi muốn tìm nhà phố ở Đà Nẵng khoảng 20 tỷ', 'session_id': 'u2', 'actor': 'customer'})
r6 = bot.process({'message': 'Gần biển', 'session_id': 'u2', 'actor': 'customer'})
r7 = bot.process({'message': 'Đổi loại BĐS tương tự', 'session_id': 'u2', 'actor': 'customer'})
print('Đổi loại:', s(r7))
print('Quick:', r7.get('quick_replies'))
r8 = bot.process({'message': 'Studio', 'session_id': 'u2', 'actor': 'customer'})
print('Studio:', s(r8))
print('Quick:', r8.get('quick_replies'))
