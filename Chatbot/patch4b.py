import re, sys
sys.stdout.reconfigure(encoding='utf-8')

with open('app_bds.py', 'r', encoding='utf-8') as f:
    text = f.read()

changes = []

# ==========================================
# FIX A, B, C, D, E: Enhance analyze() with exact action/intent detection
# ==========================================
old_analyze_top = '''        t = normalize_text(message)
        t_ascii = normalize_ascii(message)  # FIX 1: Support non-diacritics
        entities: Dict[str, Any] = {}'''

new_analyze_top = '''        t = normalize_text(message)
        t_ascii = normalize_ascii(message)  # FIX 1: Support non-diacritics
        entities: Dict[str, Any] = {}

        # EXACT ACTION MAPPING (User Story A, B, C, D, E)
        exact_actions = {
            "mở rộng sang sơn trà/ngũ hành sơn": ("search/expand_beach_areas", "search"),
            "mo rong sang son tra ngu hanh son": ("search/expand_beach_areas", "search"),
            "mở rộng khu vực biển": ("search/expand_beach_areas", "search"),
            "mở rộng sơn trà": ("search/expand_beach_areas", "search"),
            "mở rộng ngũ hành sơn": ("search/expand_beach_areas", "search"),
            
            "xem căn hộ phù hợp nhất": ("search/best_apartments", "search"),
            "xem can ho phu hop nhat": ("search/best_apartments", "search"),
            "xem căn phù hợp nhất": ("search/best_apartments", "search"),
            "xem can phu hop nhat": ("search/best_apartments", "search"),
            
            "xem tất cả bđs gần biển": ("search/all_near_beach", "search"),
            "xem tat ca bds gan bien": ("search/all_near_beach", "search"),
            "tất cả bđs gần biển": ("search/all_near_beach", "search"),
            "bđs gần biển": ("search/all_near_beach", "search"),
            "bds gan bien": ("search/all_near_beach", "search"),
            
            "đổi sang nhà phố gần biển": ("search/switch_house_near_beach", "search"),
            "doi sang nha pho gan bien": ("search/switch_house_near_beach", "search"),
            
            "khoảng 4 tỷ": ("search/price_4b", "search"),
            "khoang 4 ty": ("search/price_4b", "search"),
            "tầm 4 tỷ": ("search/price_4b", "search"),
            "tam 4 ty": ("search/price_4b", "search"),
            "4 tỷ": ("search/price_4b", "search"),
            "4 ty": ("search/price_4b", "search"),
        }
        
        if t in exact_actions:
            intent, context = exact_actions[t]
            return {"intent": intent, "context": context, "entities": {}, "confidence": 1.0, "raw": t}
        if t_ascii in exact_actions:
            intent, context = exact_actions[t_ascii]
            return {"intent": intent, "context": context, "entities": {}, "confidence": 1.0, "raw": t}'''

if old_analyze_top in text:
    text = text.replace(old_analyze_top, new_analyze_top)
    changes.append("A-E: Exact action mapping added to analyze()")
else:
    changes.append("A-E: SKIP - analyze top not found")

# ==========================================
# FIX F: Update search_response signature and mode
# ==========================================
old_search_resp_sig = '    def search_response(\n        self, ranked: List[Dict], entities: Dict, missing: List[str], relax_info: Dict\n    ) -> Tuple[str, List[str]]:'
# Wait, let me check the exact format in the file.
# It might be on multiple lines.

import sys
sys.stdout.reconfigure(encoding='utf-8')

# Let's use a simpler replace for search_response if possible
text = re.sub(r'def search_response\(\s*self,\s*ranked:\s*List\[Dict\],\s*entities:\s*Dict,\s*missing:\s*List\[str\],\s*relax_info:\s*Dict\s*\)\s*->\s*Tuple\[str,\s*List\[str\]\]:', 
              'def search_response(self, ranked: List[Dict], entities: Dict, missing: List[str], relax_info: Dict, mode: str = "default") -> Tuple[str, List[str]]:', 
              text)
changes.append("F: search_response signature updated via regex")

# Logic replacement
old_prefix_block = '''        count = len(ranked)
        prefix = f"Có vài lựa chọn khá hợp ({count} kết quả phù hợp nhất):"'''
new_prefix_block = '''        count = len(ranked)
        if mode == "list_all":
            prefix = ""
        else:
            prefix = f"Có vài lựa chọn khá hợp ({count} kết quả phù hợp nhất):"'''

if old_prefix_block in text:
    text = text.replace(old_prefix_block, new_prefix_block)
    changes.append("F: search_response prefix logic updated")

# Action list_all update
old_list_all = '''        if action == "bds/list_all_properties":
            ranked = [
                r
                for r in self.data.fetch_properties({}, limit=18)
                if not self.data.is_dirty_listing(r)
            ]
            draft, quick = self.responses.search_response(ranked, {}, [], {})
            enhanced = f"Mình đang hiển thị một số BĐS nổi bật/mới nhất cho bạn:<br>{draft}"'''

new_list_all = '''        if action == "bds/list_all_properties":
            ranked = [
                r
                for r in self.data.fetch_properties({}, limit=18)
                if not self.data.is_dirty_listing(r)
            ]
            # Use mode="list_all" to avoid duplicate prefix
            draft, quick = self.responses.search_response(ranked[:5], {}, [], {}, mode="list_all")
            enhanced = f"Mình đang hiển thị một số BĐS nổi bật/mới nhất cho bạn:<br>{draft}"'''

if old_list_all in text:
    text = text.replace(old_list_all, new_list_all)
    changes.append("F: list_all_properties updated for clean response")

with open('app_bds.py', 'w', encoding='utf-8') as f:
    f.write(text)

print("PATCH4B DONE")
for c in changes:
    print(" -", c)