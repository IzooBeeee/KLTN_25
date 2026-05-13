import os
import re

with open('app_bds.py', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Add no_result_context
old_memory = '    last_no_result_filters: Dict[str, Any] = field(default_factory=dict)  # filters that returned 0 results'
new_memory = '    last_no_result_filters: Dict[str, Any] = field(default_factory=dict)  # filters that returned 0 results\n    no_result_context: Dict[str, Any] = field(default_factory=lambda: {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0})'
text = text.replace(old_memory, new_memory)

# 2. Add handlers for escape buttons in QUICK_ACTION_MAP
old_quick_map = '''            (["xem lai goi tin", "xem goi tin", "bang gia goi tin"],                                      "goi_tin/list_packages"),
        ]'''
new_quick_map = '''            (["xem lai goi tin", "xem goi tin", "bang gia goi tin"],                                      "goi_tin/list_packages"),
            (["xem can ho phu hop nhat", "xem can phu hop nhat"],                                         "search/escape_best"),
            (["xem tat ca bds gan bien"],                                                                 "search/escape_all_beach"),
            (["doi sang nha pho gan bien", "nha pho gan bien"],                                           "search/escape_townhouse_beach"),
        ]'''
text = text.replace(old_quick_map, new_quick_map)

# 3. Add logic for escape buttons in process()
old_is_relax = '''        is_relax_budget   = any(k in msg_ascii_lower for k in ["noi ngan sach", "noi gia", "tang ngan sach", "tang gia"])'''
new_is_relax = '''        is_escape_best    = action_type == "search/escape_best" or any(k in msg_ascii_lower for k in ["xem can ho phu hop", "xem can phu hop"])
        is_escape_all_b   = action_type == "search/escape_all_beach" or "xem tat ca bds gan bien" in msg_ascii_lower
        is_escape_th_b    = action_type == "search/escape_townhouse_beach" or "doi sang nha pho gan bien" in msg_ascii_lower
        is_relax_budget   = any(k in msg_ascii_lower for k in ["noi ngan sach", "noi gia", "tang ngan sach", "tang gia"])'''
text = text.replace(old_is_relax, new_is_relax)

# 4. Handle escape buttons block
old_is_all_prop_block = '''        if is_all_prop:'''
new_is_all_prop_block = '''        if is_escape_best:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            base = deepcopy(mem.last_search_filters or {})
            base.pop("lifestyle", None)
            base.pop("locations", None)
            base.pop("refinement_type", None)
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info)
            draft_esc = "Mình quay lại danh sách các căn hộ phù hợp ban đầu cho bạn:<br>" + draft_esc
            esc_a = {"intent": "search_property", "context": "search", "entities": base}
            self.memory.update(session_id, message, draft_esc, esc_a, ranked)
            return self.wrap(draft_esc, esc_a, ranked[:6], quick_esc, start)

        if is_escape_all_b:
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            base = {"lifestyle": ["near_beach"], "locations": ["sơn trà", "ngũ hành sơn"]}
            ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
            draft_esc, quick_esc = self.responses.search_response(ranked, base, [], relax_info)
            draft_esc = "Đây là tất cả BĐS gần biển hiện có:<br>" + draft_esc
            esc_a = {"intent": "search_property", "context": "search", "entities": base}
            self.memory.update(session_id, message, draft_esc, esc_a, ranked)
            return self.wrap(draft_esc, esc_a, ranked[:6], ["Căn hộ", "Nhà phố", "Khoảng 4 tỷ", "Đặt lịch xem nhà"], start)

        if is_escape_th_b:
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
            return self.wrap(draft_esc, esc_a, ranked[:6], quick_esc, start)

        if is_all_prop:'''
text = text.replace(old_is_all_prop_block, new_is_all_prop_block)

# 5. Fix relax budget and nearby location no_result looping logic
old_relax = '''                if ranked:
                    draft_r, quick_r = self.responses.search_response(ranked, base, [], relax_info)
                    draft_r = f"Mình đã nới ngân sách lên khoảng {new_budget_text} và tìm lại cho bạn:<br>" + draft_r
                else:
                    draft_r = f"Sau khi nới ngân sách 20% (≈{new_budget_text}), mình vẫn chưa thấy căn phù hợp. Bạn muốn đổi khu vực hoặc xem loại BĐS tương tự không?"
                    quick_r = ["Tìm khu vực gần đó", "Đổi loại BĐS tương tự"]
                    ranked = []'''
new_relax = '''                if ranked:
                    draft_r, quick_r = self.responses.search_response(ranked, base, [], relax_info)
                    draft_r = f"Mình đã nới ngân sách lên khoảng {new_budget_text} và tìm lại cho bạn:<br>" + draft_r
                else:
                    mem.no_result_context["tried_actions"].append("relax_budget")
                    if "nearby_location" in mem.no_result_context["tried_actions"]:
                        draft_r = "Mình đã thử nới ngân sách và mở rộng khu vực nhưng vẫn chưa có căn hộ gần biển phù hợp. Bạn có thể xem các căn hộ phù hợp nhất hiện có hoặc đổi sang loại BĐS gần biển khác."
                        quick_r = ["Xem căn hộ phù hợp nhất", "Đổi sang nhà phố gần biển", "Xem tất cả BĐS gần biển"]
                    else:
                        draft_r = f"Sau khi nới ngân sách lên khoảng {new_budget_text}, mình vẫn chưa thấy căn phù hợp. Bạn muốn mở rộng khu vực hoặc đổi sang nhà phố không?"
                        quick_r = ["Mở rộng sang Sơn Trà/Ngũ Hành Sơn", "Đổi sang nhà phố gần biển", "Xem căn hộ phù hợp nhất"]
                    ranked = []'''
text = text.replace(old_relax, new_relax)

old_nearby = '''                if ranked:
                    draft_nb, quick_nb = self.responses.search_response(ranked, base, [], relax_info)
                    draft_nb = f"Mình thử mở rộng sang khu vực lân cận ({nearby_txt}):<br>" + draft_nb
                else:
                    draft_nb = f"Mình đã mở sang {nearby_txt} nhưng vẫn chưa có kết quả phù hợp. Bạn muốn nới ngân sách hoặc đổi loại BĐS không?"
                    quick_nb = ["Nới ngân sách 20%", "Đổi loại BĐS tương tự"]
                    ranked = []'''
new_nearby = '''                if ranked:
                    draft_nb, quick_nb = self.responses.search_response(ranked, base, [], relax_info)
                    draft_nb = f"Mình thử mở rộng sang khu vực lân cận ({nearby_txt}):<br>" + draft_nb
                else:
                    mem.no_result_context["tried_actions"].append("nearby_location")
                    if "relax_budget" in mem.no_result_context["tried_actions"]:
                        draft_nb = "Mình đã thử nới ngân sách và mở rộng khu vực nhưng vẫn chưa có căn hộ gần biển phù hợp. Bạn có thể xem các căn hộ phù hợp nhất hiện có hoặc đổi sang loại BĐS gần biển khác."
                        quick_nb = ["Xem căn hộ phù hợp nhất", "Đổi sang nhà phố gần biển", "Xem tất cả BĐS gần biển"]
                    else:
                        draft_nb = f"Mình đã mở sang {nearby_txt} nhưng vẫn chưa có kết quả. Bạn muốn nới ngân sách thêm không?"
                        quick_nb = ["Nới ngân sách thêm", "Đổi sang nhà phố gần biển", "Xem tất cả BĐS gần biển"]
                    ranked = []'''
text = text.replace(old_nearby, new_nearby)

# 6. Fix similar type and studio no_result
old_similar = '''                    # Suggest next alt type; set pending_followup so user can pick it
                    next_alts = alt_types[1:]
                    draft_st = f"Không có {alt_types[0]} phù hợp. Bạn muốn thử {', '.join(next_alts) or 'loại khác'} không?"
                    quick_st = [t.capitalize() for t in next_alts[:1]] + ["Nới ngân sách 20%"]
                    ranked = []
                    # Store pending so next user answer maps to a type search
                    mem.pending_followup = f"similar_type:{','.join(next_alts)}"'''
new_similar = '''                    mem.no_result_context["tried_actions"].append("similar_type")
                    next_alts = alt_types[1:]
                    draft_st = f"Không có {alt_types[0]} phù hợp. Bạn muốn thử {', '.join(next_alts) or 'loại khác'} không?"
                    if "studio" in next_alts:
                        quick_st = ["Studio", "Xem tất cả BĐS gần biển", "Xem căn hộ phù hợp nhất"]
                    else:
                        quick_st = [t.capitalize() for t in next_alts[:1]] + ["Xem căn hộ phù hợp nhất", "Tìm căn hộ 4 tỷ"]
                    ranked = []
                    mem.pending_followup = f"similar_type:{','.join(next_alts)}"'''
text = text.replace(old_similar, new_similar)

old_studio = '''            if not ranked_studio:
                base_s2 = deepcopy(base_s)
                base_s2.pop("title_keyword", None)
                base_s2.pop("keyword", None)
                all_rows = self.data.fetch_properties(base_s2, limit=20)
                ranked_studio = [r for r in all_rows if "studio" in (r.get("tieu_de") or "").lower()
                                 or "studio" in (r.get("mo_ta") or "").lower()]
                if ranked_studio:
                    ranked_studio = self.recommender.rank(ranked_studio, base_s2)
            
            if ranked_studio:
                draft_stu, quick_stu = self.responses.search_response(ranked_studio, base_s, [], ri_studio)
                draft_stu = f"Mình đổi sang loại Studio cho bạn:<br>" + draft_stu
                mem.pending_followup = None
            else:
                draft_stu = "Không tìm thấy căn studio nào phù hợp. Bạn muốn nới ngân sách hoặc đổi khu vực không?"
                quick_stu = ["Nới ngân sách 20%", "Tìm khu vực gần đó"]
                mem.pending_followup = None
            stu_a = {"intent": "refine_search", "context": "search", "entities": base_s}
            self.memory.update(session_id, message, draft_stu, stu_a, ranked_studio)
            return self.wrap(draft_stu, stu_a, ranked_studio[:6], quick_stu, start)'''
new_studio = '''            if not ranked_studio:
                base_s2 = deepcopy(base_s)
                base_s2.pop("title_keyword", None)
                base_s2.pop("keyword", None)
                all_rows = self.data.fetch_properties(base_s2, limit=20)
                ranked_studio = [r for r in all_rows if "studio" in (r.get("tieu_de") or "").lower()
                                 or "studio" in (r.get("mo_ta") or "").lower()]
                if ranked_studio:
                    ranked_studio = self.recommender.rank(ranked_studio, base_s2)
            
            if ranked_studio:
                draft_stu, quick_stu = self.responses.search_response(ranked_studio, base_s, [], ri_studio)
                draft_stu = f"Mình đổi sang loại Studio cho bạn:<br>" + draft_stu
                mem.pending_followup = None
            else:
                mem.no_result_context["tried_actions"].append("studio")
                draft_stu = "Hiện dữ liệu chưa có studio phù hợp theo ngân sách/khu vực này. Mình gợi ý bạn xem các căn hộ phù hợp nhất hiện có hoặc xem tất cả BĐS gần biển."
                quick_stu = ["Xem căn hộ phù hợp nhất", "Xem tất cả BĐS gần biển", "Tìm căn hộ 4 tỷ"]
                mem.pending_followup = None
            stu_a = {"intent": "refine_search", "context": "search", "entities": base_s}
            self.memory.update(session_id, message, draft_stu, stu_a, ranked_studio)
            return self.wrap(draft_stu, stu_a, ranked_studio[:6], quick_stu, start)'''
text = text.replace(old_studio, new_studio)

# 7. Modify response logic for Gần biển no_result 
old_near_beach_response = '''                    return [], {"level": "none", "message": "Mình chưa thấy căn đúng tiêu chí gần biển. Bạn muốn mở rộng sang Sơn Trà/Ngũ Hành Sơn hoặc nới ngân sách không?"}'''
new_near_beach_response = '''                    if mem: mem.no_result_context["reason"] = "near_beach"
                    return [], {"level": "none", "message": "Mình chưa thấy căn hộ đúng tiêu chí gần biển. Bạn muốn thử nới ngân sách, mở rộng khu vực biển hoặc xem các căn phù hợp nhất hiện có không?"}'''
text = text.replace(old_near_beach_response, new_near_beach_response)

# Also update search_response quick replies for near_beach no_result
old_no_result = '''        if not items:
            msg = relax_info.get("message") if relax_info and relax_info.get("message") else self.no_result_message(entities)
            return msg, [
                "Nới ngân sách 20%",
                "Tìm khu vực gần đó",
                "Đổi loại BĐS tương tự",
            ]'''
new_no_result = '''        if not items:
            msg = relax_info.get("message") if relax_info and relax_info.get("message") else self.no_result_message(entities)
            qr = ["Nới ngân sách 20%", "Mở rộng sang Sơn Trà/Ngũ Hành Sơn", "Xem căn hộ phù hợp nhất"] if relax_info and relax_info.get("message") == "Mình chưa thấy căn hộ đúng tiêu chí gần biển. Bạn muốn thử nới ngân sách, mở rộng khu vực biển hoặc xem các căn phù hợp nhất hiện có không?" else ["Nới ngân sách 20%", "Tìm khu vực gần đó", "Đổi loại BĐS tương tự"]
            return msg, qr'''
text = text.replace(old_no_result, new_no_result)

# Clear no_result_context on explicit search
old_search_process = '''        if ranked:
            enhanced = draft  # LLM failure must not break the result
        self.memory.update(session_id, message, enhanced, analysis, ranked)'''
new_search_process = '''        if ranked:
            enhanced = draft  # LLM failure must not break the result
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
        self.memory.update(session_id, message, enhanced, analysis, ranked)'''
text = text.replace(old_search_process, new_search_process)

with open('app_bds.py', 'w', encoding='utf-8') as f:
    f.write(text)
print("PATCH 2 SUCCESSFUL")