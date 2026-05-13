import sys; sys.path.insert(0,'.')
from app_bds import BDSChatbot
import json
bot = BDSChatbot()
r = bot.process({'message':'Tôi muốn tìm căn hộ ở Đà Nẵng khoảng 4 tỷ', 'session_id': 'c2', 'role': 'khach-hang'})
print(json.dumps(r, ensure_ascii=False, indent=2))