import re, sys
sys.stdout.reconfigure(encoding='utf-8')

with open('app_bds.py', 'r', encoding='utf-8') as f:
    text = f.read()

changes = []

# ==========================================
# FIX A, B, C, D, E: Enhance analyze() with exact action/intent detection
# ==========================================
# We will insert a section for exact button text matching early in analyze()

old_analyze_top = '''        t = normalize_text(text)
        t_ascii = normalize_text_ascii(text)
        entities = {}'''

new_analyze_top = '''        t = normalize_text(text)
        t_ascii = normalize_text_ascii(text)
        entities = {}

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
            return {"intent": intent, "context": context, "entities": {}, "confidence": 1.0}
        if t_ascii in exact_actions:
            intent, context = exact_actions[t_ascii]
            return {"intent": intent, "context": context, "entities": {}, "confidence": 1.0}'''

if old_analyze_top in text:
    text = text.replace(old_analyze_top, new_analyze_top)
    changes.append("A-E: Exact action mapping added to analyze()")
else:
    changes.append("A-E: SKIP - analyze top not found")

# ==========================================
# FIX F: Update search_response to handle mode="list_all"
# ==========================================
old_search_resp_sig = '    def search_response(self, ranked: List[Dict], entities: Dict, missing: List[str], relax_info: Dict) -> Tuple[str, List[str]]:'
new_search_resp_sig = '    def search_response(self, ranked: List[Dict], entities: Dict, missing: List[str], relax_info: Dict, mode: str = "default") -> Tuple[str, List[str]]:'
if old_search_resp_sig in text:
    text = text.replace(old_search_resp_sig, new_search_resp_sig)
    changes.append("F: search_response signature updated")

old_search_resp_prefix = '''        if not ranked:
            return self.no_results(entities, relax_info)

        count = len(ranked)
        prefix = f"Có vài lựa chọn khá hợp ({count} kết quả phù hợp nhất):"'''

new_search_resp_prefix = '''        if not ranked:
            return self.no_results(entities, relax_info)

        count = len(ranked)
        if mode == "list_all":
            prefix = ""
        else:
            prefix = f"Có vài lựa chọn khá hợp ({count} kết quả phù hợp nhất):"'''

if old_search_resp_prefix in text:
    text = text.replace(old_search_resp_prefix, new_search_resp_prefix)
    changes.append("F: search_response prefix logic updated")

# ==========================================
# FIX G: Set selected_property after valuation
# ==========================================
# We already replaced the valuation block in Patch 3, so we need to find the new one.
# It is at: return self.wrap(draft_val, analysis, ranked[:6], ["Tìm BĐS cùng khu vực", "Đặt lịch xem nhà"], start)

old_val_return = '                self.memory.update(session_id, message, draft_val, analysis, ranked)\n                return self.wrap(draft_val, analysis, ranked[:6], ["Tìm BĐS cùng khu vực", "Đặt lịch xem nhà"], start)'
new_val_return = '''                if ranked:
                    mem.selected_property = ranked[0]
                    mem.last_results = ranked
                self.memory.update(session_id, message, draft_val, analysis, ranked)
                return self.wrap(draft_val, analysis, ranked[:6], ["Tìm BĐS cùng khu vực", "Đặt lịch xem nhà"], start)'''

if old_val_return in text:
    text = text.replace(old_val_return, new_val_return)
    changes.append("G: selected_property set after valuation")

# ==========================================
# FIX H: Handle new actions in BDSChatbot.process()
# ==========================================
# We need to insert the handling of new search actions before the default search flow

old_process_search_start = '        # Default search flow'
new_process_search_start = '''        # Handle specialized search actions (User Story A, B, C, D, E)
        spec_intent = analysis.get("intent")
        if spec_intent and spec_intent.startswith("search/"):
            # Clear no-result chains for exit actions (User Story H)
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            mem.pending_followup = None
            
            ent_s = {}
            msg_prefix = ""
            mode = "default"
            
            if spec_intent == "search/expand_beach_areas":
                ent_s = deepcopy(mem.last_no_result_filters or mem.last_search_filters or {})
                ent_s["locations"] = ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê", "an hải"]
                msg_prefix = "Mình đã mở rộng tìm kiếm sang khu vực Sơn Trà và Ngũ Hành Sơn cho bạn:"
                
            elif spec_intent == "search/best_apartments":
                # User Story B: Ignore no_result_context, find best apartments
                ent_s = {"property_type": "căn hộ", "locations": ["đà nẵng"]}
                if mem.last_successful_search_results:
                    # Logic: if we have history, try to stay close to that budget
                    prev_p = [r.get("gia", 4000000000) for r in mem.last_successful_search_results if r.get("gia")]
                    if prev_p:
                        avg_p = sum(prev_p) / len(prev_p)
                        ent_s["price_max"] = int(avg_p * 1.2)
                else:
                    ent_s["price_max"] = 5000000000
                msg_prefix = "Mình quay lại các căn hộ phù hợp nhất cho bạn:"
                
            elif spec_intent == "search/all_near_beach":
                # User Story C: Search all property types near beach, broad locations
                ent_s = {"lifestyle": ["near_beach"], "locations": ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê", "võ nguyên giáp", "an hải", "phạm văn đồng"]}
                msg_prefix = "Mình hiển thị một số BĐS gần biển cho bạn:"
                
            elif spec_intent == "search/switch_house_near_beach":
                # User Story D: Switch to house near beach
                ent_s = {"property_type": "nhà riêng", "lifestyle": ["near_beach"], "locations": ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê", "an hải"]}
                msg_prefix = "Mình chuyển sang tìm nhà phố gần biển cho bạn:"
                
            elif spec_intent == "search/price_4b":
                # User Story E: Search around 4 billion
                ent_s = deepcopy(mem.last_search_filters or {})
                ent_s["price_min"] = 3500000000
                ent_s["price_max"] = 4500000000
                if not ent_s.get("property_type") and mem.active_context != "all-properties":
                    ent_s["property_type"] = "căn hộ"
                msg_prefix = "Mình lọc các BĐS khoảng 4 tỷ cho bạn:"

            ranked, relax_info = self.find_ranked_properties(ent_s, "search_property", mem=mem)
            if ranked:
                draft, quick = self.responses.search_response(ranked, ent_s, [], relax_info)
                enhanced = f"{msg_prefix}<br>{draft}"
                # If enhanced by Gemini, it might look better, but let's keep prefix for now
                self.memory.update(session_id, message, enhanced, {"intent": "search_property", "entities": ent_s}, ranked)
                return self.wrap(enhanced, {"intent": "search_property"}, ranked[:6], quick, start)
            else:
                # If specialized search fails
                if spec_intent == "search/expand_beach_areas":
                    fail_msg = "Mình đã thử mở rộng sang Sơn Trà/Ngũ Hành Sơn nhưng vẫn chưa có căn phù hợp. Bạn có thể xem các căn hộ phù hợp nhất hiện có hoặc xem tất cả BĐS gần biển."
                    quick = ["Xem căn hộ phù hợp nhất", "Xem tất cả BĐS gần biển", "Tìm căn hộ 4 tỷ"]
                elif spec_intent == "search/switch_house_near_beach":
                    fail_msg = "Mình chưa thấy nhà phố gần biển phù hợp. Bạn muốn xem tất cả BĐS gần biển hoặc quay lại căn hộ phù hợp nhất không?"
                    quick = ["Xem tất cả BĐS gần biển", "Xem căn hộ phù hợp nhất", "Tìm căn hộ 4 tỷ"]
                else:
                    fail_msg = f"Mình chưa tìm thấy kết quả phù hợp cho yêu cầu này. Bạn muốn thử tìm kiếm khác không?"
                    quick = ["Tìm căn hộ 4 tỷ", "Xem tất cả BĐS", "Định giá BĐS"]
                
                self.memory.update(session_id, message, fail_msg, analysis, [])
                return self.wrap(fail_msg, analysis, [], quick, start)

        # Default search flow'''

if old_process_search_start in text:
    text = text.replace(old_process_search_start, new_process_search_start)
    changes.append("H: specialized search actions handled in process()")

# ==========================================
# FIX F (part 2): list_all_properties mode
# ==========================================
old_list_all = '''        if action == "bds/list_all_properties":
            ranked = [
                r
                for r in self.data.fetch_properties({}, limit=18)
                if not self.data.is_dirty_listing(r)
            ]
            draft, quick = self.responses.search_response(ranked, {}, [], {})
            enhanced = f"Mình đang hiển thị một số BĐS nổi bật/mới nhất cho bạn:<br>{draft}"
            self.memory.update(session_id, message, enhanced, analysis, ranked)
            return self.wrap(enhanced, analysis, ranked[:6], quick, start)'''

new_list_all = '''        if action == "bds/list_all_properties":
            ranked = [
                r
                for r in self.data.fetch_properties({}, limit=18)
                if not self.data.is_dirty_listing(r)
            ]
            # Use mode="list_all" to avoid duplicate prefix
            draft, quick = self.responses.search_response(ranked[:5], {}, [], {}, mode="list_all")
            enhanced = f"Mình đang hiển thị một số BĐS nổi bật/mới nhất cho bạn:<br>{draft}"
            self.memory.update(session_id, message, enhanced, analysis, ranked)
            return self.wrap(enhanced, analysis, ranked[:6], quick, start)'''

if old_list_all in text:
    text = text.replace(old_list_all, new_list_all)
    changes.append("F: list_all_properties updated for clean response")

with open('app_bds.py', 'w', encoding='utf-8') as f:
    f.write(text)

print("PATCH4 DONE")
for c in changes:
    print(" -", c)