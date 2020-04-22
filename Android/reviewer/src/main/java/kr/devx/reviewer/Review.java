package kr.devx.reviewer;

public class Review {
    private final String TAG;
    private final String USER;
    private final double RATING;
    private final String TITLE;
    private final String CONTENT;

    public static class Builder {
        private final String USER;
        private final double RATING;
        private String TAG = "";
        private String TITLE = "";
        private String CONTENT = "";

        public Builder(String _user, double _rating) {
            this.USER = _user;
            this.RATING = _rating;
        }

        public Builder tag(String _tag) {
            this.TAG = _tag;
            return this;
        }

        public Builder title(String _title) {
            this.TITLE = _title;
            return this;
        }

        public Builder content(String _content) {
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

    public String getTag() {
        return TAG;
    }

    public String getUser() {
        return USER;
    }

    public double getRating() {
        return RATING;
    }

    public String getTitle() {
        return TITLE;
    }

    public String getContent() {
        return CONTENT;
    }
}
