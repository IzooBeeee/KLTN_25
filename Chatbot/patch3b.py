import sys
sys.stdout.reconfigure(encoding='utf-8')

with open('app_bds.py', 'r', encoding='utf-8') as f:
    text = f.read()

# FIX T5: 150m² when in valuation context
# The problem: "150m²" is a pure area string - analyze() returns area entities but intent=general_bds
# The valuation fallback catches intent in [search_property, general_bds, empty, refine_search]
# BUT the merged entities then may not have property_type if we only take cur_ents
# Let's check: the valuation_state.entities should have property_type + location from step 4

# The real bug: area parsing "150m²" → area_min or area_max set
# The normalize_text and parse_area should handle "150m²" → area_min=150
# Then merged with valuation_state.entities which has property_type+location
# So it should work... unless parse_area does not handle "150m²"

# Let's check parse_area:
changes = []

old_area_patt = '''    # "X m2", "Xm²", "khoảng Xm²" etc.
    m = re.search(r"(?:khoảng\\s*)?(\\d+(?:[.,]\\d+)?)\\s*m[²2]", t)'''
if old_area_patt in text:
    changes.append("area pattern present - skip")
else:
    changes.append("area pattern not as expected")

# Add explicit "150m²" style parsing check - the pattern should already handle it
# Let's look at the valuation context fallback more carefully
# The issue: in T5, session t5 already had a previous search done (bot.process in T7 for t7 uses t7 session)
# Wait no - T5 uses session 't5', T7 uses 't7' - they're separate
# The real issue: '150m²' - normalize_text turns ² to ² but does the regex handle it?

# Let's add a stronger pattern for just a number followed by m²
old_parse_area = '''def parse_area(text: str) -> Tuple[Optional[int], Optional[int]]:'''
if old_parse_area in text:
    changes.append("parse_area function found")

# Check for m2 vs m² handling
# More likely issue: the valuation context routing
# When user sends "150m²", analyze() runs:
# - normalize_text → "150m²" 
# - parse_area → should detect area_min/area_max
# - intent stays "general_bds" since no other keyword
# - Then in process(), mem.active_context == "valuation" check triggers
# BUT: the check is AFTER the router.route() call which handles refine_search etc
# So "150m²" → intent=general_bds → valuation context check → YES!
# But then in the valuation block, prev_val_ents should be merged...
# WAIT: the merge block is in the fallback line:
# if mem.active_context == "valuation" and analysis.get("intent") in [...]

# Let me re-read the code to check if merge actually runs
# From patch E:
# old_valuation_fallback = '''if mem.active_context == "valuation" and analysis.get("intent") in ["search_property", "general_bds", "empty"]:
#     analysis["intent"] = "valuation"'''
# new has: prev_val_ents = mem.valuation_state.get("entities", {})
#          merged_ents = deepcopy(prev_val_ents)
#          ...
#          analysis["entities"] = merged_ents
#          analysis["intent"] = "valuation"

# Then the valuation block runs again:
# if analysis.get("intent") == "valuation":
#   mem.active_context = "valuation"
#   ents = analysis.get("entities", {})
#   # Merge with previous valuation_state entities
#   if mem.valuation_state.get("status") == "collecting":
#       prev_ents = mem.valuation_state.get("entities", {})
#       for k, v in prev_ents.items():
#           if k not in ents or not ents[k]:
#               ents[k] = v

# So there are TWO merges. Both should work. But we also check:
# 'has_area = bool(ents.get("area_min") or ents.get("area_max"))'
# Does parse_area("150m²") return area_min=150?

# Check the parse_area function
import re

# Test locally
import unicodedata
def normalize_text(t):
    t = (t or "").strip().lower()
    return re.sub(r"\s+", " ", t)

def parse_area_test(text):
    t = normalize_text(text)
    # Look for pattern: number + m² or m2
    m = re.search(r"(?:khoảng\s*)?(\d+(?:[.,]\d+)?)\s*m[²2]", t)
    if m:
        return int(float(m.group(1).replace(",", ".")))
    return None

result = parse_area_test("150m²")
print(f"parse_area('150m²') = {result}")
result2 = parse_area_test("150m2")
print(f"parse_area('150m2') = {result2}")

with open('app_bds.py', 'w', encoding='utf-8') as f:
    f.write(text)
print("PATCH3B done (no file changes, just diagnosis)")