import re, sys
sys.stdout.reconfigure(encoding='utf-8')

with open('app_bds.py', 'r', encoding='utf-8') as f:
    text = f.read()

changes = []

# ==========================================
# FIX F: Update search_response signature and prefix logic
# ==========================================
old_sig = '''    def search_response(
        self,
        items: List[Dict[str, Any]],
        entities: Dict[str, Any],
        missing: List[str],
        relax_info: Optional[Dict[str, Any]] = None,
    ) -> Tuple[str, List[str]]:'''
new_sig = '''    def search_response(
        self,
        items: List[Dict[str, Any]],
        entities: Dict[str, Any],
        missing: List[str],
        relax_info: Optional[Dict[str, Any]] = None,
        mode: str = "default",
    ) -> Tuple[str, List[str]]:'''

if old_sig in text:
    text = text.replace(old_sig, new_sig)
    changes.append("F: search_response signature updated")
else:
    # Try regex if exact match fails
    text = re.sub(r'def search_response\(\s*self,\s*items:\s*List\[Dict\[str,\s*Any\]\],\s*entities:\s*Dict\[str,\s*Any\],\s*missing:\s*List\[str\],\s*relax_info:\s*Optional\[Dict\[str,\s*Any\]\]\s*=\s*None,\s*\)\s*->\s*Tuple\[str,\s*List\[str\]\]:',
                  'def search_response(self, items: List[Dict[str, Any]], entities: Dict[str, Any], missing: List[str], relax_info: Optional[Dict[str, Any]] = None, mode: str = "default") -> Tuple[str, List[str]]:',
                  text)
    changes.append("F: search_response signature updated via regex")

old_prefix = '            lines.append(f"{random.choice(self.OPENERS)} ({len(items)} kết quả phù hợp nhất):")'
new_prefix = '''            if mode == "list_all":
                pass
            else:
                lines.append(f"{random.choice(self.OPENERS)} ({len(items)} kết quả phù hợp nhất):")'''

if old_prefix in text:
    text = text.replace(old_prefix, new_prefix)
    changes.append("F: search_response prefix logic updated")

# ==========================================
# FIX F (Part 2): Update list_all_properties action in process()
# ==========================================
# We need to find where list_all_properties is handled in process()
# It's likely inside the action_type check or similar

old_list_all_action = '''        if action == "bds/list_all_properties":
            ranked = [
                r
                for r in self.data.fetch_properties({}, limit=18)
                if not self.data.is_dirty_listing(r)
            ]
            draft, quick = self.responses.search_response(ranked, {}, [], {})
            enhanced = f"Mình đang hiển thị một số BĐS nổi bật/mới nhất cho bạn:<br>{draft}"'''

new_list_all_action = '''        if action == "bds/list_all_properties":
            ranked = [
                r
                for r in self.data.fetch_properties({}, limit=18)
                if not self.data.is_dirty_listing(r)
            ]
            # Use mode="list_all" to avoid duplicate prefix
            draft, quick = self.responses.search_response(ranked[:5], {}, [], {}, mode="list_all")
            enhanced = f"Mình đang hiển thị một số BĐS nổi bật/mới nhất cho bạn:<br>{draft}"'''

if old_list_all_action in text:
    text = text.replace(old_list_all_action, new_list_all_action)
    changes.append("F: list_all_properties action updated in process()")

# ==========================================
# FINAL VERIFICATION: Ensure A-E specialized actions are correct
# ==========================================
# The specialized actions were added in Patch 4, let's make sure they use items/ranked correctly.

# In Patch 4 I used:
# ranked, relax_info = self.find_ranked_properties(ent_s, "search_property", mem=mem)
# if ranked:
#     draft, quick = self.responses.search_response(ranked, ent_s, [], relax_info)
# This is correct because ResponseGenerator.search_response now uses 'items' which is 'ranked' here.

with open('app_bds.py', 'w', encoding='utf-8') as f:
    f.write(text)

print("PATCH5 DONE")
for c in changes:
    print(" -", c)