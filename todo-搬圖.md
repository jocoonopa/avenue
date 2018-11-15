# 搬圖

-  Digital Ocean 174 那台開一個新的 laravel site `avenue.jocoonopa.com`
-  Github 開一個新的 `avenue-laravel` private repository
- `avenue.jocoonopa.com` 僅具有`圖床`和 `Redis-Broadcast` 兩個功能

# Entity

Img 以及 Desimg 增加 `is_trashed` 欄位 (一定要開成 INT, 不然 doctrine 抓不到)，透過 Query 直接進行 updated 

```sql

UPDATE desimg 
LEFT JOIN goods_passport 
ON desimg.id = goods_passport.desimg_id 
SET desimg.yahooName = 'yyy' 
WHERE goods_passport.status_id in (2, 10) and goods_passport.updateAt <= '2018-05-05'/*

```


# 清除圖片

## 下載過期的圖片

整包從 ftp 抓下來

## 刪除圖片

Loop all Img and Desimg where `is_trashed = 1`, and **unlink file**


```php

$max = 1000;
$size = 100;

$qb = $this->getEntityManager()->createQueryBuilder();

$qb->select('*')->from('WoojinGoodsBundle:Img', 'img')
    ->where(
        $qb->expr()->eq('img.isTrashed', true)
    )
;

$total = $qb->getQuery()->getSingleScalarResult();

for ($i = 0; $i < $total; $i = $i + $size) {
    if ($i > $max) {
        break;
    }

    $qb
        ->setFirstResult($i)
        ->setMaxResults($i + $size)
    ;

    $imgs = $qb->getQuery()->getResult();

    $imgs->each(function ($img) {
        echo $img->getPath() . ': is-trashed = ' . $img->getIsTrashed() . "<br/>";
    });
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

## 搬圖片

- 下載壓縮的 `.tar`
- 壓縮的 `.tar` 上傳透過 ftp upload 到 public/img, 然後解壓縮
- 移除 `.tar`



