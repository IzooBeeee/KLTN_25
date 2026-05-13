import sys
sys.path.insert(0, '.')
sys.stdout.reconfigure(encoding='utf-8')
from app_bds import BDSChatbot, normalize_text, parse_area

bot = BDSChatbot()
bot.process({'message': 'Dinh gia BDS', 'session_id': 'td5', 'actor': 'customer'})
mem = bot.memory.get('td5')
print("active_context:", mem.active_context, "| val_state:", mem.valuation_state)

bot.process({'message': 'Dat nen Lien Chieu', 'session_id': 'td5', 'actor': 'customer'})
mem = bot.memory.get('td5')
print("active_context:", mem.active_context, "| val_state:", mem.valuation_state)

rule_a = bot.nlu.analyze('150m2', mem)
print("NLU for 150m2:", rule_a)

r3 = bot.process({'message': '150m2', 'session_id': 'td5', 'actor': 'customer'})
print("reply:", r3.get('response','')[:120])