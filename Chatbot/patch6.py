import re, sys
sys.stdout.reconfigure(encoding='utf-8')

with open('app_bds.py', 'r', encoding='utf-8') as f:
    text = f.read()

changes = []

# ==========================================
# FIX A, B, C, D, E, F: Update ActionRouter.route with correct logic
# ==========================================

# 1. Update is_escape_best (Story B)
old_escape_best = '''        if is_escape_best:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            base = deepcopy(mem.last_search_filters or {})
            base.pop("lifestyle", None)
            base.pop("locations", None)
            base.pop("refinement_type", None)
            base.pop("refinement", None)
            base.pop("superlative", None)
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info)
            draft_esc = "Mình quay lại danh sách các căn hộ phù hợp ban đầu cho bạn:<br>" + draft_esc
            esc_a = {"intent": "search_property", "context": "search", "entities": base}
            self.memory.update(session_id, message, draft_esc, esc_a, ranked)
            return self.wrap(draft_esc, esc_a, ranked[:6], quick_esc, start)'''

new_escape_best = '''        if is_escape_best:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            mem.pending_followup = None
            # Story B: Use last_successful_search_results if they were apartments
            base = {"property_type": "căn hộ", "locations": ["đà nẵng"]}
            if mem.last_successful_search_results:
                prev_p = [r.get("gia", 4000000000) for r in mem.last_successful_search_results if r.get("gia")]
                if prev_p: base["price_max"] = int(sum(prev_p)/len(prev_p) * 1.2)
            else:
                base["price_max"] = 5000000000
            
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info, mode="list_all")
            draft_esc = "Mình quay lại các căn hộ phù hợp nhất cho bạn:<br>" + draft_esc
            esc_a = {"intent": "search_property", "context": "search", "entities": base}
            self.memory.update(session_id, message, draft_esc, esc_a, ranked)
            return self.wrap(draft_esc, esc_a, ranked[:6], quick_esc, start)'''

if old_escape_best in text:
    text = text.replace(old_escape_best, new_escape_best)
    changes.append("B: ActionRouter.is_escape_best updated")

# 2. Update is_escape_all_b (Story C)
old_escape_all_b = '''        if is_escape_all_b:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            base = {"lifestyle": ["near_beach"], "locations": ["sơn trà", "ngũ hành sơn"]}
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info)
            draft_esc = "Đây là tất cả BĐS gần biển hiện có:<br>" + draft_esc
            esc_a = {"intent": "search_property", "context": "search", "entities": base}
            self.memory.update(session_id, message, draft_esc, esc_a, ranked)
            return self.wrap(draft_esc, esc_a, ranked[:6], ["Căn hộ", "Nhà phố", "Khoảng 4 tỷ", "Đặt lịch xem nhà"], start)'''

new_escape_all_b = '''        if is_escape_all_b:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            mem.pending_followup = None
            # Story C: Broad beach search
            base = {"lifestyle": ["near_beach"], "locations": ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê", "võ nguyên giáp", "an hải", "phạm văn đồng"]}
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info, mode="list_all")
            draft_esc = "Mình hiển thị một số BĐS gần biển cho bạn:<br>" + draft_esc
            esc_a = {"intent": "search_property", "context": "search", "entities": base}
            self.memory.update(session_id, message, draft_esc, esc_a, ranked)
            return self.wrap(draft_esc, esc_a, ranked[:6], ["Rẻ hơn", "Rộng hơn", "Đặt lịch xem nhà"], start)'''

if old_escape_all_b in text:
    text = text.replace(old_escape_all_b, new_escape_all_b)
    changes.append("C: ActionRouter.is_escape_all_b updated")

# 3. Update is_escape_th_b (Story D)
old_escape_th_b = '''        if is_escape_th_b:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            base = deepcopy(mem.last_search_filters or {})
            base.pop("price_max", None)
            base.pop("price_min", None)
            base["property_type"] = "nhà phố"
            base["lifestyle"] = ["near_beach"]
            base["locations"] = ["sơn trà", "ngũ hành sơn"]
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info)
            draft_esc = "Mình chuyển sang tìm nhà phố gần biển cho bạn:<br>" + draft_esc
            esc_a = {"intent": "search_property", "context": "search", "entities": base}
            self.memory.update(session_id, message, draft_esc, esc_a, ranked)
            return self.wrap(draft_esc, esc_a, ranked[:6], quick_esc, start)'''

new_escape_th_b = '''        if is_escape_th_b:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            mem.pending_followup = None
            # Story D: House near beach, no context leak
            base = {"property_type": "nhà riêng", "lifestyle": ["near_beach"], "locations": ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê", "an hải"]}
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            if ranked:
                draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info, mode="list_all")
                draft_esc = "Mình chuyển sang tìm nhà phố gần biển cho bạn:<br>" + draft_esc
                esc_a = {"intent": "search_property", "context": "search", "entities": base}
                self.memory.update(session_id, message, draft_esc, esc_a, ranked)
                return self.wrap(draft_esc, esc_a, ranked[:6], quick_esc, start)
            else:
                fail_msg = "Mình chưa thấy nhà phố gần biển phù hợp. Bạn muốn xem tất cả BĐS gần biển hoặc quay lại căn hộ phù hợp nhất không?"
                quick = ["Xem tất cả BĐS gần biển", "Xem căn hộ phù hợp nhất", "Tìm căn hộ 4 tỷ"]
                self.memory.update(session_id, message, fail_msg, {"intent": "search_property"}, [])
                return self.wrap(fail_msg, {"intent": "search_property"}, [], quick, start)'''

if old_escape_th_b in text:
    text = text.replace(old_escape_th_b, new_escape_th_b)
    changes.append("D: ActionRouter.is_escape_th_b updated")

# 4. Update is_all_prop (Story F)
old_is_all_prop = '''        if is_all_prop:
            base = {}
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            if ranked:
                draft_all, quick_all = self.responses.search_response(ranked, base, [], relax_info)
                draft_all = "Mình đang hiển thị một số BĐS nổi bật/mới nhất cho bạn:<br>" + draft_all
            else:
                draft_all = "Hiện tại chưa có BĐS nào phù hợp."
                quick_all = ["Căn hộ", "Nhà phố", "Gần biển", "Khoảng 4 tỷ"]
            all_a = {"intent": "search_property", "context": "search", "entities": {}}
            self.memory.update(session_id, message, draft_all, all_a, ranked)
            return self.wrap(draft_all, all_a, ranked[:6], ["Căn hộ", "Nhà phố", "Gần biển", "Khoảng 4 tỷ"], start)'''

new_is_all_prop = '''        if is_all_prop:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            mem.pending_followup = None
            base = {}
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            if ranked:
                # Mode list_all to avoid prefix duplication
                draft_all, quick_all = self.responses.search_response(ranked[:5], base, [], relax_info, mode="list_all")
                draft_all = "Mình đang hiển thị một số BĐS nổi bật/mới nhất cho bạn:<br>" + draft_all
            else:
                draft_all = "Hiện tại chưa có BĐS nào phù hợp."
                quick_all = ["Tìm căn hộ 4 tỷ", "Định giá BĐS", "Đặt lịch xem nhà"]
            all_a = {"intent": "search_property", "context": "search", "entities": {}}
            self.memory.update(session_id, message, draft_all, all_a, ranked)
            return self.wrap(draft_all, all_a, ranked[:6], quick_all, start)'''

if old_is_all_prop in text:
    text = text.replace(old_is_all_prop, new_is_all_prop)
    changes.append("F: ActionRouter.is_all_prop updated")

# 5. Add is_price_4b (Story E)
old_is_similar_prop = '        is_similar_prop   = any(k in msg_ascii_lower for k in ["tim can tuong tu", "can tuong tu", "tuong tu can nay", "tuong tu can do"])'
new_is_similar_prop = old_is_similar_prop + '\n        is_price_4b       = any(k in msg_ascii_lower for k in ["khoang 4 ty", "tam 4 ty", "4 ty"])'
if old_is_similar_prop in text:
    text = text.replace(old_is_similar_prop, new_is_similar_prop)
    changes.append("E: is_price_4b flag added")

old_price_4b_block = '''        if is_similar_prop:'''
new_price_4b_block = '''        if is_price_4b:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            mem.pending_followup = None
            base = deepcopy(mem.last_search_filters or {})
            base["price_min"] = 3500000000
            base["price_max"] = 4500000000
            if not base.get("property_type"): base["property_type"] = "căn hộ"
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            if ranked:
                draft, quick = self.responses.search_response(ranked, base, [], relax_info, mode="list_all")
                draft = "Mình lọc các BĐS khoảng 4 tỷ cho bạn:<br>" + draft
                a_4b = {"intent": "search_property", "context": "search", "entities": base}
                self.memory.update(session_id, message, draft, a_4b, ranked)
                return self.wrap(draft, a_4b, ranked[:6], quick, start)
            else:
                draft = "Mình chưa tìm thấy BĐS khoảng 4 tỷ phù hợp. Bạn muốn thử nới ngân sách hoặc xem khu vực khác không?"
                self.memory.update(session_id, message, draft, {"intent": "search_property"}, [])
                return self.wrap(draft, {"intent": "search_property"}, [], ["Nới ngân sách 20%", "Tìm khu vực gần đó"], start)

        if is_similar_prop:'''
if old_price_4b_block in text:
    text = text.replace(old_price_4b_block, new_price_4b_block)
    changes.append("E: price_4b handler added to ActionRouter")

# 6. Add is_expand_beach (Story A)
old_is_price_4b = '        is_price_4b       = any(k in msg_ascii_lower for k in ["khoang 4 ty", "tam 4 ty", "4 ty"])'
new_is_price_4b = old_is_price_4b + '\n        is_expand_beach   = any(k in msg_ascii_lower for k in ["mo rong sang son tra", "mo rong khu vuc bien", "mo rong son tra", "mo rong ngu hanh son"])'
if old_is_price_4b in text:
    text = text.replace(old_is_price_4b, new_is_price_4b)
    changes.append("A: is_expand_beach flag added")

old_expand_beach_block = '''        if is_price_4b:'''
new_expand_beach_block = '''        if is_expand_beach:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            mem.pending_followup = None
            base = deepcopy(mem.last_no_result_filters or mem.last_search_filters or {})
            base["locations"] = ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê", "an hải"]
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            if ranked:
                draft, quick = self.responses.search_response(ranked, base, [], relax_info, mode="list_all")
                draft = "Mình đã mở rộng tìm kiếm sang khu vực Sơn Trà và Ngũ Hành Sơn cho bạn:<br>" + draft
                a_exp = {"intent": "search_property", "context": "search", "entities": base}
                self.memory.update(session_id, message, draft, a_exp, ranked)
                return self.wrap(draft, a_exp, ranked[:6], quick, start)
            else:
                fail_msg = "Mình đã thử mở rộng sang Sơn Trà/Ngũ Hành Sơn nhưng vẫn chưa có căn phù hợp. Bạn có thể xem các căn hộ phù hợp nhất hiện có hoặc xem tất cả BĐS gần biển."
                quick = ["Xem căn hộ phù hợp nhất", "Xem tất cả BĐS gần biển", "Tìm căn hộ 4 tỷ"]
                self.memory.update(session_id, message, fail_msg, {"intent": "search_property"}, [])
                return self.wrap(fail_msg, {"intent": "search_property"}, [], quick, start)

        if is_price_4b:'''
if old_expand_beach_block in text:
    text = text.replace(old_expand_beach_block, new_expand_beach_block)
    changes.append("A: expand_beach handler added to ActionRouter")

# ==========================================
# FIX I: Cleanup redundant handling in BDSChatbot.process()
# ==========================================
# I added a specialized search actions block in Patch 4, I will remove it 
# because now it is handled in ActionRouter.route() which is called via self.router.route()

old_process_spec_block = '''        # Handle specialized search actions (User Story A, B, C, D, E)
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
                return self.wrap(fail_msg, analysis, [], quick, start)'''

if old_process_spec_block in text:
    text = text.replace(old_process_spec_block, '')
    changes.append("I: cleanup spec search block in process()")

# ==========================================
# FIX J: Update no_result_message quick replies
# ==========================================
old_no_result_qr = 'qr = ["Nới ngân sách 20%", "Mở rộng sang Sơn Trà/Ngũ Hành Sơn", "Xem căn hộ phù hợp nhất"] if relax_info and relax_info.get("message") == "Mình chưa thấy căn hộ đúng tiêu chí gần biển. Bạn muốn thử nới ngân sách, mở rộng khu vực biển hoặc xem các căn phù hợp nhất hiện có không?" else ["Nới ngân sách 20%", "Tìm khu vực gần đó", "Đổi loại BĐS tương tự"]'
new_no_result_qr = 'qr = ["Nới ngân sách 20%", "Mở rộng sang Sơn Trà/Ngũ Hành Sơn", "Xem căn hộ phù hợp nhất", "Xem tất cả BĐS gần biển"] if relax_info and relax_info.get("message") == "Mình chưa thấy căn hộ đúng tiêu chí gần biển. Bạn muốn thử nới ngân sách, mở rộng khu vực biển hoặc xem các căn phù hợp nhất hiện có không?" else ["Nới ngân sách 20%", "Mở rộng sang Sơn Trà/Ngũ Hành Sơn", "Xem căn hộ phù hợp nhất", "Đổi loại BĐS tương tự"]'
if old_no_result_qr in text:
    text = text.replace(old_no_result_qr, new_no_result_qr)
    changes.append("J: no_result quick replies updated")

with open('app_bds.py', 'w', encoding='utf-8') as f:
    f.write(text)

print("PATCH6 DONE")
for c in changes:
    print(" -", c)