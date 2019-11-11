using System;
using System.Diagnostics;
using System.IO;
using System.Net;
using System.Text;
using Newtonsoft.Json.Linq;

namespace Reviewer
{
    public class Reviewer
    {

        public static void newReview(string key, Review review)
        {
            networkAsync(key, review);
        }

        private static async void networkAsync(string key, Review review)
        {
            try
            {
                UTF8Encoding encoding = new UTF8Encoding();

                string postData = "service_key=" + key;
                postData += "&review_user=" + review.getUser();
                postData += "&review_tag=" + review.getTag();
                postData += "&review_rating=" + review.getRating();
                postData += "&review_title=" + review.getTitle();
                postData += "&review_content=" + review.getContent();

                byte[] data = encoding.GetBytes(postData);

                WebRequest request = WebRequest.Create("https://api.devx.kr/Reviewer/v1/review_new.php");
                request.Method = "POST";
                request.ContentType = "application/x-www-form-urlencoded";
                request.Headers["ContentLength"] = data.Length.ToString();

                Stream stream = await request.GetRequestStreamAsync();
                stream.Write(data, 0, data.Length);

                WebResponse response = await request.GetResponseAsync();

                stream = response.GetResponseStream();

                if (stream == null)
                {
                    try
                    {
                        Debug.WriteLine("CATCHER FAIL : response null");
                    }
                    catch (Exception e)
                    {

                    }
                    return;
                }

                StreamReader ar = new StreamReader(stream);

                JObject resultObject = JObject.Parse(ar.ReadToEnd());

                if (resultObject.GetValue("result").Value<int>() == 0)
                {
                    try
                    {
                        Debug.WriteLine("CATCHER SUCCESS : " + resultObject.GetValue("review_index").Value<string>());
                    }
                    catch (Exception e)
                    {

                    }
                }
                else
                {
                    Debug.WriteLine("CATCHER FAIL : " + resultObject.GetValue("error").Value<string>());
                    Debug.WriteLine("CATCHER FAIL : " + resultObject.GetValue("error_debug").Value<string>());
                }
            }
            catch (Exception e)
            {
                try
                {
                    Debug.WriteLine("CATCHER FAIL : " + e);
                }
                catch (Exception)
                {
                }
            }
        }
    }
    
    public class Review {
        private readonly string TAG;
        private readonly string USER;
        private readonly double RATING;
        private readonly string TITLE;
        private readonly string CONTENT;

        public class Builder {
            public readonly string USER;
            public readonly double RATING;
            public string TAG = "";
            public string TITLE = "";
            public string CONTENT = "";

            public Builder(string _user, double _rating) {
                this.USER = _user;
                this.RATING = _rating;
            }

            public Builder tag(string _tag) {
                this.TAG = _tag;
                return this;
            }

            public Builder title(string _title) {
                this.TITLE = _title;
                return this;
            }

            public Builder content(string _content) {
                this.CONTENT = _content;
                return this;
            }

            public Review build() {
                return new Review(this);
            }
        }

        private Review(Builder builder) {
            TAG = builder.TAG;
            USER = builder.USER;
            RATING = builder.RATING;
            TITLE = builder.TITLE;
            CONTENT = builder.CONTENT;
        }

        public string getTag() {
            return TAG;
        }

        public string getUser() {
            return USER;
        }

        public double getRating() {
            return RATING;
        }

        public string getTitle() {
            return TITLE;
        }

        public string getContent() {
            return CONTENT;
        }
    }
}