import os
import re

with open('app_bds.py', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Update rank to handle superlatives
old_rank = '''        ranked = [x for x in ranked if x.get("recommendation_score", 0) > -50]
        return sorted(
            ranked, key=lambda x: x.get("recommendation_score", 0), reverse=True
        )'''
new_rank = '''        ranked = [x for x in ranked if x.get("recommendation_score", 0) > -50]
        superlative = entities.get("superlative")
        if superlative == "cheapest":
            return sorted(ranked, key=lambda x: float(x.get("gia") or float("inf")))
        elif superlative == "largest":
            return sorted(ranked, key=lambda x: float(x.get("dien_tich") or 0), reverse=True)
        return sorted(
            ranked, key=lambda x: x.get("recommendation_score", 0), reverse=True
        )'''
text = text.replace(old_rank, new_rank)

# 2. Update reason to strictly check near_beach
old_reason = '''        if entities.get("lifestyle"):
            lifestyles = []
            for lf in entities.get("lifestyle")[:2]:
                if lf == "near_beach": lifestyles.append("gần biển")
                elif lf == "center": lifestyles.append("gần trung tâm")
                elif lf == "luxury": lifestyles.append("phân khúc cao cấp")
                elif lf == "family": lifestyles.append("phù hợp gia đình")
                elif lf == "investment": lifestyles.append("phù hợp đầu tư")
                else: lifestyles.append(lf)
            reasons.append(", ".join(lifestyles))'''
new_reason = '''        if entities.get("lifestyle"):
            lifestyles = []
            for lf in entities.get("lifestyle")[:2]:
                if lf == "near_beach":
                    text_str = " ".join([str(item.get("tieu_de") or ""), str(item.get("mo_ta") or ""), str(item.get("dia_chi") or "")]).lower()
                    if any(kw in text_str for kw in ["biển", "bãi tắm", "sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê", "vỗ nguyên giáp", "phạm văn đồng", "an hải"]):
                        lifestyles.append("gần biển")
                elif lf == "center": lifestyles.append("gần trung tâm")
                elif lf == "luxury": lifestyles.append("phân khúc cao cấp")
                elif lf == "family": lifestyles.append("phù hợp gia đình")
                elif lf == "investment": lifestyles.append("phù hợp đầu tư")
                else: lifestyles.append(lf)
            if lifestyles:
                reasons.append(", ".join(lifestyles))'''
text = text.replace(old_reason, new_reason)

# 3. Add is_same_loc and is_all_prop in process()
old_is_relax = '''        is_relax_budget   = any(k in msg_ascii_lower for k in ["noi ngan sach", "noi gia", "tang ngan sach", "tang gia"])'''
new_is_relax = '''        is_all_prop       = any(k in msg_ascii_lower for k in ["xem tat ca bat dong san", "xem tat ca bds", "tat ca bat dong san", "tat ca tin", "xem tat ca tin dang", "danh sach bat dong san", "tim bds", "tim bat dong san"])
        is_same_loc       = any(k in msg_ascii_lower for k in ["tim bds cung khu vuc", "tim bat dong san cung khu vuc", "tim can cung khu", "cung khu vuc"])
        is_relax_budget   = any(k in msg_ascii_lower for k in ["noi ngan sach", "noi gia", "tang ngan sach", "tang gia"])'''
text = text.replace(old_is_relax, new_is_relax)

# 4. Handle is_all_prop and is_same_loc block
old_is_relax_block = '''        if is_relax_budget:'''
new_is_relax_block = '''        if is_all_prop:
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
            return self.wrap(draft_all, all_a, ranked[:6], ["Căn hộ", "Nhà phố", "Gần biển", "Khoảng 4 tỷ"], start)

        if is_same_loc:
            base = {}
            if mem.selected_property and isinstance(mem.selected_property, dict) and mem.selected_property.get("dia_chi"):
                prop = mem.selected_property
                for loc_k in DA_NANG_NEARBY_DISTRICTS:
                    if loc_k in prop["dia_chi"].lower():
                        base["location"] = loc_k
                        break
                if prop.get("loai"): base["property_type"] = prop["loai"]
            elif mem.active_context == "valuation" and mem.entities.get("location"):
                base["location"] = mem.entities.get("location")
                
            if base.get("location"):
                ranked, relax_info = self.find_ranked_properties(base, "search_property", mem=mem)
                if mem.selected_property and isinstance(mem.selected_property, dict) and mem.selected_property.get("id"):
                    ranked = [r for r in ranked if str(r.get("id")) != str(mem.selected_property.get("id"))]
                if ranked:
                    draft_loc, quick_loc = self.responses.search_response(ranked, base, [], relax_info)
                    draft_loc = f"Mình tìm thêm vài BĐS cùng khu vực {base['location'].title()} cho bạn:<br>" + draft_loc
                else:
                    draft_loc = f"Mình chưa tìm thấy thêm căn nào khác ở khu vực {base['location'].title()}."
                    quick_loc = ["Tìm BĐS", "Định giá BĐS"]
                loc_a = {"intent": "search_property", "context": "search", "entities": base}
                self.memory.update(session_id, message, draft_loc, loc_a, ranked)
                return self.wrap(draft_loc, loc_a, ranked[:6], quick_loc, start)
            else:
                draft_loc = "Bạn muốn tìm BĐS cùng khu vực nào? Ví dụ Hải Châu, Sơn Trà hoặc Thanh Khê."
                self.memory.update(session_id, message, draft_loc, {"intent": "general_bds", "entities": {}}, [])
                return self.wrap(draft_loc, {"intent": "general_bds"}, [], ["Hải Châu", "Sơn Trà", "Thanh Khê"], start)

        if is_relax_budget:'''
text = text.replace(old_is_relax_block, new_is_relax_block)

# 5. Fix Valuation at the bottom
old_val_fallback = '''        # Fallback responses
        if analysis.get("intent") in [
            "ending",
            "out_of_domain",
            "empty",
            "valuation",
            "appointment",
            "general_bds",
            "posting_guide",
            "package_buy_guide",
        ]:
            draft, quick = self.responses.simple(analysis.get("intent"))'''
new_val_fallback = '''        # Fallback responses
        if analysis.get("intent") == "valuation":
            ents = analysis.get("entities", {})
            has_type = bool(ents.get("property_type"))
            has_loc = bool(ents.get("location") or ents.get("locations"))
            has_area = bool(ents.get("area_min") or ents.get("area_max"))
            if has_type and has_loc and has_area:
                ranked, _ = self.find_ranked_properties(ents, "valuation", mem=mem)
                if ranked:
                    avg_price = sum(r.get("gia", 0) for r in ranked) / len(ranked)
                    draft_val = f"Mình đã tìm {len(ranked)} tin đăng tương tự (cùng loại, khu vực) để ước tính. Giá tham khảo khoảng {money_vnd(avg_price)}.<br><i>Lưu ý: Đây chỉ là ước tính tự động, không phải thẩm định chính thức.</i>"
                else:
                    draft_val = "Hiện tại hệ thống chưa có đủ tin đăng tương tự ở khu vực này để đưa ra mức giá tham khảo chính xác."
                self.memory.update(session_id, message, draft_val, analysis, ranked)
                return self.wrap(draft_val, analysis, ranked[:6], ["Tìm BĐS cùng khu vực", "Đặt lịch xem nhà"], start)
            else:
                draft_val = "Mình có thể hỗ trợ ước tính giá tham khảo. Bạn cho mình thêm vài thông tin nhé:<br>1. Loại BĐS: căn hộ/nhà phố/đất nền...<br>2. Khu vực: quận/phường hoặc địa chỉ gần đúng.<br>3. Diện tích.<br>4. Số phòng ngủ/tình trạng nếu có."
                self.memory.update(session_id, message, draft_val, analysis, [])
                return self.wrap(draft_val, analysis, [], ["Căn hộ Hải Châu 60m²", "Nhà phố Sơn Trà 100m²", "Đất nền Liên Chiểu"], start)

        if analysis.get("intent") in [
            "ending",
            "out_of_domain",
            "empty",
            "appointment",
            "general_bds",
            "posting_guide",
            "package_buy_guide",
        ]:
            draft, quick = self.responses.simple(analysis.get("intent"))'''
text = text.replace(old_val_fallback, new_val_fallback)

# 6. Fix responses.search_response to use specific text for refinement
old_refine_responses = '''            "cheaper": "Mình đã tìm các lựa chọn rẻ hơn cho bạn:",
            "larger": "Mình đã tìm các lựa chọn rộng hơn cho bạn:",'''
new_refine_responses = '''            "cheaper": "Mình sắp xếp các căn phù hợp theo giá thấp đến cao cho bạn:",
            "larger": "Mình ưu tiên các căn có diện tích lớn hơn trong cùng tiêu chí:",'''
text = text.replace(old_refine_responses, new_refine_responses)

# 7. Fix near_beach response to be strict
old_near_beach_response = '''            "near_beach": "Mình ưu tiên các lựa chọn gần biển cho bạn:",'''
new_near_beach_response = '''            "near_beach": "Mình ưu tiên các lựa chọn gần biển cho bạn:",''' # We will handle strict in find_ranked_properties
text = text.replace(old_near_beach_response, new_near_beach_response)

with open('app_bds.py', 'w', encoding='utf-8') as f:
    f.write(text)
print("PATCH SUCCESSFUL")