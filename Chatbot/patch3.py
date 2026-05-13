import re, sys
sys.stdout.reconfigure(encoding='utf-8')

with open('app_bds.py', 'r', encoding='utf-8') as f:
    text = f.read()

changes = []

# ==========================================
# FIX A: Add valuation_state to ChatMemory
# ==========================================
old = '    no_result_context: Dict[str, Any] = field(default_factory=lambda: {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0})'
new = old + '\n    valuation_state: Dict[str, Any] = field(default_factory=lambda: {"status": "idle", "entities": {}})'
if old in text:
    text = text.replace(old, new)
    changes.append("A: valuation_state added to ChatMemory")
else:
    changes.append("A: SKIP - valuation_state already exists or old not found")

# ==========================================
# FIX B: Add "goi y nha" and "view dep" as search_property keywords in LIFESTYLE
# Also add "gợi ý nhà" search detection in analyze()
# ==========================================
old_lifestyle = '        \"view đẹp\": \"view\",'
new_lifestyle = '        \"view đẹp\": \"view\",\n        \"view dep\": \"view\",'
if old_lifestyle in text:
    text = text.replace(old_lifestyle, new_lifestyle)
    changes.append("B: view dep (ascii) added to LIFESTYLE")

# ==========================================
# FIX C: detect "gợi ý nhà gần biển/view đẹp" as search_property NOT empty/general
# Add early detection in analyze() method before package keywords
# ==========================================
old_analyze_start = '''        # FIX 1: Check package keywords with both diacritics and non-diacritics
        package_keywords = ['''
new_analyze_start = '''        # SEARCH LIFESTYLE EARLY DETECTION: "Gợi ý nhà gần biển, view đẹp"
        search_lifestyle_kw = [
            "gợi ý nhà", "goi y nha", "gợi ý bđs", "goi y bds",
            "tìm nhà gần biển", "tim nha gan bien", "nhà gần biển", "nha gan bien",
            "bđs gần biển", "bds gan bien", "gần biển view", "gan bien view",
        ]
        if any(k in t or k in t_ascii for k in search_lifestyle_kw):
            intent, context = "search_property", "search"
            lifestyles = []
            if "gần biển" in t or "gan bien" in t_ascii:
                lifestyles.append("near_beach")
            if "view đẹp" in t or "view dep" in t_ascii or "view" in t:
                lifestyles.append("view")
            if lifestyles:
                entities["lifestyle"] = lifestyles
            if not entities.get("locations"):
                entities["locations"] = ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê"]

        # FIX 1: Check package keywords with both diacritics and non-diacritics
        package_keywords = ['''
if old_analyze_start in text:
    text = text.replace(old_analyze_start, new_analyze_start)
    changes.append("C: early search lifestyle detection added in analyze()")
else:
    changes.append("C: SKIP - analyze start not found")

# ==========================================
# FIX D: Valuation state machine
# When user enters valuation intent, set mem.active_context = "valuation" and valuation_state
# ==========================================
old_valuation_block = '''        if analysis.get("intent") == "valuation":
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
                return self.wrap(draft_val, analysis, [], ["Căn hộ Hải Châu 60m²", "Nhà phố Sơn Trà 100m²", "Đất nền Liên Chiểu"], start)'''

new_valuation_block = '''        if analysis.get("intent") == "valuation":
            mem.active_context = "valuation"
            ents = analysis.get("entities", {})
            # Merge with previous valuation_state entities if collecting
            if mem.valuation_state.get("status") == "collecting":
                prev_ents = mem.valuation_state.get("entities", {})
                for k, v in prev_ents.items():
                    if k not in ents or not ents[k]:
                        ents[k] = v
                analysis["entities"] = ents
            has_type = bool(ents.get("property_type"))
            has_loc = bool(ents.get("location") or ents.get("locations"))
            has_area = bool(ents.get("area_min") or ents.get("area_max"))
            if has_type and has_loc and has_area:
                # Reset valuation_state
                mem.valuation_state = {"status": "idle", "entities": {}}
                try:
                    ranked, _ = self.find_ranked_properties(ents, "valuation", mem=mem)
                except Exception as e:
                    logger.warning("valuation_find_fail | %s", e)
                    ranked = []
                if ranked:
                    avg_price = sum(r.get("gia", 0) for r in ranked) / len(ranked)
                    draft_val = f"Mình đã tìm {len(ranked)} tin đăng tương tự (cùng loại, khu vực) để ước tính. Giá tham khảo khoảng {money_vnd(avg_price)}.<br><i>Lưu ý: Đây chỉ là ước tính tự động, không phải thẩm định chính thức.</i>"
                else:
                    draft_val = "Hiện tại hệ thống chưa có đủ tin đăng tương tự ở khu vực này để đưa ra mức giá tham khảo chính xác."
                self.memory.update(session_id, message, draft_val, analysis, ranked)
                return self.wrap(draft_val, analysis, ranked[:6], ["Tìm BĐS cùng khu vực", "Đặt lịch xem nhà"], start)
            else:
                # Save partial info and ask for missing
                mem.valuation_state = {"status": "collecting", "entities": ents}
                if has_type and has_loc and not has_area:
                    prop_type = ents.get("property_type", "BĐS")
                    loc = ents.get("location") or (ents.get("locations") or ["khu vực này"])[0]
                    draft_val = f"Bạn cho mình biết diện tích {prop_type} ở {loc} khoảng bao nhiêu m² để ước tính sát hơn nhé."
                    qr_val = ["60m²", "100m²", "150m²", "200m²"]
                elif has_type and not has_loc:
                    prop_type = ents.get("property_type", "BĐS")
                    draft_val = f"Bạn muốn định giá {prop_type} ở khu vực nào tại Đà Nẵng?"
                    qr_val = ["Hải Châu", "Sơn Trà", "Ngũ Hành Sơn", "Liên Chiểu"]
                else:
                    draft_val = "Mình có thể hỗ trợ ước tính giá tham khảo. Bạn cho mình thêm vài thông tin nhé:<br>1. Loại BĐS: căn hộ/nhà phố/đất nền...<br>2. Khu vực: quận/phường hoặc địa chỉ gần đúng.<br>3. Diện tích."
                    qr_val = ["Căn hộ Hải Châu 60m²", "Nhà phố Sơn Trà 100m²", "Đất nền Liên Chiểu 150m²"]
                self.memory.update(session_id, message, draft_val, analysis, [])
                return self.wrap(draft_val, analysis, [], qr_val, start)'''

if old_valuation_block in text:
    text = text.replace(old_valuation_block, new_valuation_block)
    changes.append("D: valuation state machine fixed")
else:
    changes.append("D: SKIP - valuation block not found")

# ==========================================
# FIX E: In valuation context, if user provides area after partial valuation, route to valuation not search
# Add area-only detection in valuation context
# ==========================================
old_valuation_fallback = '''        if mem.active_context == "valuation" and analysis.get("intent") in ["search_property", "general_bds", "empty"]:
            analysis["intent"] = "valuation"'''
new_valuation_fallback = '''        if mem.active_context == "valuation" and analysis.get("intent") in ["search_property", "general_bds", "empty", "refine_search"]:
            # Check if user is providing missing valuation info (area, type, location)
            prev_val_ents = mem.valuation_state.get("entities", {})
            merged_ents = deepcopy(prev_val_ents)
            cur_ents = analysis.get("entities", {})
            for k, v in cur_ents.items():
                if v:
                    merged_ents[k] = v
            analysis["entities"] = merged_ents
            analysis["intent"] = "valuation"'''
if old_valuation_fallback in text:
    text = text.replace(old_valuation_fallback, new_valuation_fallback)
    changes.append("E: valuation context fallback - merge entities from valuation_state")
else:
    changes.append("E: SKIP - valuation fallback not found")

# ==========================================
# FIX F: "Gần biển" standalone - if no context, do broad beach search instead of failing
# ==========================================
old_refine_intent = '''        elif any(k in t for k in ["rẻ hơn", "rộng hơn", "gần biển", "gần trung tâm"]):
            intent, context = "refine_search", (
                "search" if memory.active_context == "search" else "general"
            )
            entities["refinement"] = True
            if "rẻ hơn" in t:
                entities["refinement_type"] = "cheaper"
            elif "rộng hơn" in t:
                entities["refinement_type"] = "larger"
            elif "gần biển" in t:
                entities["refinement_type"] = "near_beach"
            elif "gần trung tâm" in t:
                entities["refinement_type"] = "center"'''
new_refine_intent = '''        elif any(k in t for k in ["rẻ hơn", "rộng hơn", "gần biển", "gần trung tâm"]):
            if "gần biển" in t and memory.active_context not in ["search"] and not memory.last_search_filters:
                # No prior search context → treat as broad beach search
                intent, context = "search_property", "search"
                lifestyles = entities.get("lifestyle") or []
                if "near_beach" not in lifestyles:
                    lifestyles.append("near_beach")
                entities["lifestyle"] = lifestyles
                if not entities.get("locations"):
                    entities["locations"] = ["sơn trà", "ngũ hành sơn", "mỹ an", "mỹ khê"]
            else:
                intent, context = "refine_search", (
                    "search" if memory.active_context == "search" else "general"
                )
                entities["refinement"] = True
                if "rẻ hơn" in t:
                    entities["refinement_type"] = "cheaper"
                elif "rộng hơn" in t:
                    entities["refinement_type"] = "larger"
                elif "gần biển" in t:
                    entities["refinement_type"] = "near_beach"
                elif "gần trung tâm" in t:
                    entities["refinement_type"] = "center"'''
if old_refine_intent in text:
    text = text.replace(old_refine_intent, new_refine_intent)
    changes.append("F: Gần biển standalone → broad beach search when no context")
else:
    changes.append("F: SKIP - refine intent block not found")

# ==========================================
# FIX G: Wrap the default search flow in try/except so LLM failures don't crash it
# ==========================================
old_search_flow = '''        # Default search flow
        entities_s = analysis.get("entities") or {}
        missing = self.missing_info(entities_s, analysis.get("intent"))
        ranked, relax_info = self.find_ranked_properties(
            entities_s, analysis.get("intent"), mem=mem, raw_text=normalize_text(message)
        )
        # Preserve no-result filters for later quick replies
        if not ranked and entities_s:
            mem.last_no_result_filters = deepcopy(entities_s)
        draft, quick = self.responses.search_response(
            ranked, entities_s, missing, relax_info
        )
        try:
            enhanced = self.gemini.enhance(draft, message, analysis)
        except Exception:
            enhanced = draft  # LLM failure must not break the result
        if ranked:
            enhanced = draft  # LLM failure must not break the result
            mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
        self.memory.update(session_id, message, enhanced, analysis, ranked)
        return self.wrap(enhanced, analysis, ranked[:6], quick, start)'''
new_search_flow = '''        # Default search flow
        try:
            entities_s = analysis.get("entities") or {}
            missing = self.missing_info(entities_s, analysis.get("intent"))
            ranked, relax_info = self.find_ranked_properties(
                entities_s, analysis.get("intent"), mem=mem, raw_text=normalize_text(message)
            )
            # Preserve no-result filters for later quick replies
            if not ranked and entities_s:
                mem.last_no_result_filters = deepcopy(entities_s)
            draft, quick = self.responses.search_response(
                ranked, entities_s, missing, relax_info
            )
            try:
                enhanced = self.gemini.enhance(draft, message, analysis)
            except Exception:
                enhanced = draft  # LLM failure must not break the result
            if ranked:
                mem.no_result_context = {"base_filters": {}, "reason": None, "tried_actions": [], "retry_count": 0}
            self.memory.update(session_id, message, enhanced, analysis, ranked)
            return self.wrap(enhanced, analysis, ranked[:6], quick, start)
        except Exception as search_exc:
            logger.exception("search_flow_fail | session=%s | msg=%s | intent=%s | err=%s",
                session_id, message, analysis.get("intent"), search_exc)
            draft_fb = "Mình chưa tìm được BĐS phù hợp lúc này. Bạn thử mô tả lại yêu cầu hoặc hỏi theo mẫu: tìm căn hộ Đà Nẵng 4 tỷ."
            return self.wrap(draft_fb, analysis, [], ["Tìm BĐS", "Định giá BĐS", "Đặt lịch xem nhà"], start)'''
if old_search_flow in text:
    text = text.replace(old_search_flow, new_search_flow)
    changes.append("G: default search flow wrapped in try/except")
else:
    changes.append("G: SKIP - search flow not found")

# ==========================================
# FIX H: Also add explicit logging in the Flask /chatbot route to capture actor + analysis
# ==========================================
old_chatbot_route = '''@app.route("/chat", methods=["POST"])
def chat():
    payload = request.get_json(silent=True) or {}
    session_id = payload.get("session_id", "unknown")
    message    = payload.get("message", "")
    try:
        result = chatbot.process(payload)
        return jsonify(result)
    except Exception as exc:'''
new_chatbot_route = '''@app.route("/chat", methods=["POST"])
def chat():
    payload = request.get_json(silent=True) or {}
    session_id = payload.get("session_id", "unknown")
    message    = payload.get("message", "")
    actor      = payload.get("actor", "guest")
    try:
        result = chatbot.process(payload)
        return jsonify(result)
    except Exception as exc:'''
if old_chatbot_route in text:
    text = text.replace(old_chatbot_route, new_chatbot_route)
    changes.append("H: actor captured in /chat route for logging")
else:
    changes.append("H: SKIP - chat route not found")

with open('app_bds.py', 'w', encoding='utf-8') as f:
    f.write(text)

print("PATCH3 DONE")
for c in changes:
    print(" -", c)