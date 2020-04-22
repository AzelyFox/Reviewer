package kr.devx.reviewer;

import android.app.Activity;
import android.content.ContentValues;
import android.os.AsyncTask;
import android.util.JsonReader;
import android.util.Log;
import org.json.JSONException;
import org.json.JSONObject;

public class Reviewer {

    public static void newReview(String key, Review review) {
        ContentValues values = new ContentValues();
        values.put("service_key", key);
        values.put("review_user", review.getUser());
        values.put("review_tag", review.getTag());
        values.put("review_rating", review.getRating());
        values.put("review_title", review.getTitle());
        values.put("review_content", review.getContent());
        NetworkTask netTask = new NetworkTask("https://api.devx.kr/Reviewer/v1/review_new.php", values);
        netTask.execute();
    }

    static class NetworkTask extends AsyncTask<Void, Void, String> {
        private String url;
        private ContentValues values;

        NetworkTask(String url, ContentValues values) {
            this.url = url;
            this.values = values;
        }

        @Override
        protected String doInBackground(Void... params) {

            String result;
            try {
                RequestHttpURLConnection requestHttpURLConnection = new RequestHttpURLConnection();
                result = requestHttpURLConnection.request(url, values);
            } catch (Exception e) {
                result = null;
                Log.e("DEVX-REVIEWER","ERROR : " + e.getCause());
            }
            return result;
        }

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);
            if (s == null) {
                return;
            }
            try {
                JSONObject result = new JSONObject(s);
                int resultCode = result.getInt("result");
                if (resultCode == 0) {
                    Log.i("DEVX-REVIEWER","SUCCESS : REVIEW ADDED : " + result.getInt("review_index"));
                } else {
                    if (resultCode == -1) {
                        Log.e("DEVX-REVIEWER","FAIL : " + result.getInt("error"));
                        Log.e("DEVX-REVIEWER","FAIL : " + result.getInt("error_debug"));
                    }
                    if (resultCode == -2) {
                        Log.e("DEVX-REVIEWER","FAIL : " + result.getInt("error"));
                        Log.e("DEVX-REVIEWER","FAIL : " + result.getInt("error_debug"));
                    }
                    if (resultCode == -3) {
                        Log.e("DEVX-REVIEWER","FAIL : " + result.getInt("error"));
                        Log.e("DEVX-REVIEWER","FAIL : " + result.getInt("error_debug"));
                    }
                }
            } catch (JSONException e) {
                e.printStackTrace();
                Log.e("DEVX-REVIEWER","ERROR : " + s);
            }
        }
    }
}
