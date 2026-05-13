import re, sys
sys.stdout.reconfigure(encoding='utf-8')

with open('app_bds.py', 'r', encoding='utf-8') as f:
    text = f.read()

changes = []

# 1. Add fields to ChatMemory
old_memory = '    valuation_state: Dict[str, Any] = field(default_factory=lambda: {"status": "idle", "entities": {}})'
new_memory = old_memory + '\n    last_successful_search_results: List[Dict[str, Any]] = field(default_factory=list)'

if old_memory in text:
    text = text.replace(old_memory, new_memory)
    changes.append("ChatMemory updated with last_successful_search_results")

# 2. Update MemoryManager.update to save successful results
old_update = '''        mem.last_intent = analysis.get("intent")
        mem.active_context = analysis.get("context")
        if results:
            mem.last_results = results'''
new_update = '''        mem.last_intent = analysis.get("intent")
        mem.active_context = analysis.get("context")
        if results:
            mem.last_results = results
            # Story B: Save successful results for "Best apartment" escape flow
            mem.last_successful_search_results = results'''

if old_update in text:
    text = text.replace(old_update, new_update)
    changes.append("MemoryManager.update updated to save successful results")

with open('app_bds.py', 'w', encoding='utf-8') as f:
    f.write(text)

print("PATCH7 DONE")
for c in changes:
    print(" -", c)