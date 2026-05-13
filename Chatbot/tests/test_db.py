# test_db.py
import mysql.connector
import os
from dotenv import load_dotenv

load_dotenv(".env.bds")

print("🔍 Testing database connection...")
print(f"DB_NAME from .env: {os.getenv('BDS_DB_NAME')}")

try:
    conn = mysql.connector.connect(
        host=os.getenv("BDS_DB_HOST", "127.0.0.1"),
        port=int(os.getenv("BDS_DB_PORT", "3306")),
        database=os.getenv("BDS_DB_NAME", "be_bds_kltn_t6"),
        user=os.getenv("BDS_DB_USER", "root"),
        password=os.getenv("BDS_DB_PASSWORD", ""),
    )
    print("✅ Connected!")
    
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT DATABASE() as current_db")
    print(f"📦 Current database: {cursor.fetchone()['current_db']}")
    
    cursor.execute("SHOW TABLES LIKE 'goi_tins'")
    tables = cursor.fetchall()
    print(f"📋 Tables matching 'goi_tins': {tables}")
    
    cursor.execute("SELECT COUNT(*) as total FROM goi_tins")
    count = cursor.fetchone()['total']
    print(f"📊 Records in goi_tins: {count}")
    
    if count > 0:
        cursor.execute("SELECT * FROM goi_tins LIMIT 2")
        rows = cursor.fetchall()
        print(f"📄 Sample data: {rows}")
    
    cursor.close()
    conn.close()
    
except Exception as e:
    print(f"❌ ERROR: {e}")