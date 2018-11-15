avenue-step-3: 
    - 建立 redis + symfony and 
    - symfony print controller and 
    - symfony print model
avenue-step-4: c# 程式 decompile and listen on redis, 然後打包好點...


# 批發價

## 搜尋

- 品牌 [ids]
- 款式 [ids]
- **isAllowWholesale**

# 顯示

- `售價` + `批發價` + `圖片` + `品名` + `店名` **!! 不可以秀產編**

```php

80*3+80=320
10000/400 = 25

```
# 將主機移到 Forge 管理

如果 serverzoo 加錢就可以了事，那就從 jocoonopa.coms那邊建立一個入口，接收 redis, redis 再去和 local 的 印表機溝通

如果不能了事，那就把 digitalocean 那台當圖床來用。

丟給我 sn + 圖片 + type, 
我接收儲存並且切圖,
完成後我回給你 url


# 標籤機可以設定列印的對象

- db.printers.name
- client.setting.name = $name
- redis 


# 清除圖片

## 下載過期的圖片並且刪除

整包從 ftp 抓下來

```php
function grab_image($url,$saveto){
    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $raw=curl_exec($ch);
    curl_close ($ch);
    if(file_exists($saveto)){
        unlink($saveto);
    }
    $fp = fopen($saveto,'x');
    fwrite($fp, $raw);
    fclose($fp);
}
```

## 刪掉空的資料夾

```php

function RemoveEmptySubFolders($path)
{
    $empty=true;
    foreach (glob($path.DIRECTORY_SEPARATOR."*") as $file)
    {
        if (is_dir($file))
        {
            if (!RemoveEmptySubFolders($file)) $empty=false;
        }
        else
        {
            $empty=false;
        }
    }
    
    if ($empty) rmdir($path);
    
    return $empty;
}

```
