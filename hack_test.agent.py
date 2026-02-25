import requests
import os
import time

# --- 配置區 ---
SERVER_URL = "https://eclass.puiching.edu.mo/path/to/backup_server.php" # 修改為實際 PHP 路徑
TOKEN = "njgYRSvO4amFoMPP3K6pcbl5TQimr8mIWv1DVrEu7FmQQ4w50URwt0Xaffek"
SAVE_DIR = "./server_backup_download"

if not os.path.exists(SAVE_DIR):
    os.makedirs(SAVE_DIR)

def download_part(part_number):
    params = {'part': part_number}
    headers = {'Authorization': f'Bearer {TOKEN}'}
    
    print(f"[*] 正在請求第 {part_number} 分片...")
    
    try:
        # 使用 stream=True 處理大檔案，避免內存溢出
        with requests.get(SERVER_URL, params=params, headers=headers, stream=True) as r:
            if r.status_code == 404:
                print("[!] 伺服器回報沒有更多檔案。備份結束。")
                return False
            
            r.raise_for_status()
            
            filename = f"part_{part_number}.zip"
            filepath = os.path.join(SAVE_DIR, filename)
            
            with open(filepath, 'wb') as f:
                for chunk in r.iter_content(chunk_size=8192):
                    if chunk:
                        f.write(chunk)
            
            print(f"[+] 成功下載: {filename}")
            return True
            
    except Exception as e:
        print(f"[x] 下載錯誤: {e}")
        return False

# --- 主循環 ---
current_part = 1
while True:
    success = download_part(current_part)
    if not success:
        break
    current_part += 1
    time.sleep(1) # 稍微停頓，減輕伺服器負擔

print(f"\n任務完成。所有檔案已儲存在: {os.path.abspath(SAVE_DIR)}")