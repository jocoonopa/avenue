## 搬圖片

- 找個夜深人靜的時候，進入 cpanel 後台的 `檔案管理員`
- 壓縮所有圖檔 `.zip` 並且下載
- 上傳 `.zip` 並透過 ftp upload 到 `avenue2003.fl` 的 `public/storage/img/product`
- `cd public/storage/img/product` 然後解壓縮 `unzip -u xxx.zip`
- 移除 `.zip`
- 到資料庫把全部圖片更改 `isTrashed=1`

```sql

UPDATE img SET isTrashed=1;
UPDATE DESimg SET isTrashed=1;

```

- 進入商品管理確認無誤後，透過 cpanel 檔案管理員後台刪除所有圖片 folder
