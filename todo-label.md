重寫了...唉

```c#

// C#  
Properties.Settings.Default.myColor = Color.AliceBlue;

// C#  
Properties.Settings.Default.Save();

```


```c#

using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Web;

using Newtonsoft.Json;

namespace ConsoleApp1Post
{
    class Program
    {
        static void Main(string[] args)
        {
            //財政部電子發票API的Url
            string url = "https://www.einvoice.nat.gov.tw/BIZAPIVAN/biz";
            string appId = "";//向財政部申請的appId
            string pCode = ""; //愛心碼
 
            HttpWebRequest request = (HttpWebRequest)WebRequest.Create(url);
            request.Method = "POST";
            request.ContentType = "application/x-www-form-urlencoded";

            //必須透過ParseQueryString()來建立NameValueCollection物件，之後.ToString()才能轉換成queryString
            NameValueCollection postParams = System.Web.HttpUtility.ParseQueryString(string.Empty);
            postParams.Add("version", "1.0");
            postParams.Add("action", "preserveCodeCheck");
            postParams.Add("pCode", pCode);
            postParams.Add("TxID", Guid.NewGuid().ToString());
            postParams.Add("appId", appId);

            //Console.WriteLine(postParams.ToString());// 將取得"version=1.0&action=preserveCodeCheck&pCode=pCode&TxID=guid&appId=appId", key和value會自動UrlEncode
            //要發送的字串轉為byte[] 
            byte[] byteArray = Encoding.UTF8.GetBytes(postParams.ToString()); 
            using (Stream reqStream = request.GetRequestStream())
            {
                reqStream.Write(byteArray, 0, byteArray.Length);
            }//end using
             
            //API回傳的字串
            string responseStr = "";
            //發出Request
            using (WebResponse response = request.GetResponse())
            { 
                using (StreamReader sr = new StreamReader(response.GetResponseStream(),Encoding.UTF8))
                {
                     responseStr = sr.ReadToEnd();
                }//end using  
            }
             
            
             Console.Write(responseStr);//印出回傳字串
             Console.ReadKey();//暫停畫面

            JObject obj = (JObject) JsonConvert.DeserializeObject(responseStr);
            Console.WriteLine(Convert.ToString(obj["isExist"]));//印出isExist的值
        }
    }
}


```

