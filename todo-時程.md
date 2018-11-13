# Step1

## 2018-10-29

- ✔ User entity 增加 is_partner

**2018-11-14 03:30**

關掉外鍵檢查匯出 sql 檔案


```bash

$php app/console doctrine:schema:update --force

```

然後看起來能匯入，一堆 foreign_key 果然都變成 null 了

再次測試 doctrine

```bash

$php app/console doctrine:schema:update --dump-sql

```

因為用 `force` 整個程序就卡住了，我根本看不出來有沒有動...
既然這樣我就原始點用  `--dump-sql` 人眼看= =，只是之後自動部署怎們辦我還沒想到

**2018-11-14 03:52**

> 我真的他媽不知道 sf2 你的 schema:update 怎們弄的，資料量越大居然會越慢這不是搞笑嗎?
> 這樣我怎們部署啊???

## 二天份量

- User 登入後根據是否為 `is_partner` 導向至不同頁面

`src/Woojin/BaseBundle/Controller/BaseController.php` @recordUsersLogAction

- 批發商 store.sn 為 `@`

- `User.php@hasAuth` 直接擋掉所有 `isPartner` 的權限

**Entity**

- GoodsPassport
    - wholesale
    - isAllowWholesale

**介面**

- 進貨
- editv2

## 三天份量

建立一個 CRUD 批發商的頁面

## 一天份量

- 查貨頁面 layout + route

## 兩天份量

- 查貨頁面條件搜尋 + 頁面呈現 + 登出
