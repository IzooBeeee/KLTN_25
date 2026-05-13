import re, sys
sys.stdout.reconfigure(encoding='utf-8')

with open('app_bds.py', 'r', encoding='utf-8') as f:
    text = f.read()

changes = []

old_search_success = '''        if is_search_success:
            is_currently_booking = mem.active_context == "booking" or is_booking_flow
            mem.active_context = "search"'''
new_search_success = '''        if is_search_success:
            is_currently_booking = mem.active_context == "booking" or is_booking_flow
            mem.active_context = "search"
            # Story B: Save successful results for "Best apartment" escape flow
            mem.last_successful_search_results = results'''

if old_search_success in text:
    text = text.replace(old_search_success, new_search_success)
    changes.append("MemoryManager.update updated to save successful results")

with open('app_bds.py', 'w', encoding='utf-8') as f:
    f.write(text)

print("PATCH7B DONE")
for c in changes:
    print(" -", c)