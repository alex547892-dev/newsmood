-- Таблица новостей
CREATE TABLE IF NOT EXISTS news (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    source_url TEXT UNIQUE NOT NULL,
    source_name TEXT NOT NULL,
    title_original TEXT NOT NULL,
    content_original TEXT,
    pub_date DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Справочник настроений
CREATE TABLE IF NOT EXISTS moods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT UNIQUE NOT NULL,
    label TEXT NOT NULL
);

-- Предзаполним настроения
INSERT OR IGNORE INTO moods (code, label) VALUES
    ('original', 'Оригинал'),
    ('happy', 'Радостное 😊'),
    ('sad', 'Грустное 😢'),
    ('ironic', 'Ироничное 😏'),
    ('neutral', 'Нейтральное 😐');

-- Переписанные новости
CREATE TABLE IF NOT EXISTS news_mood (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    news_id INTEGER NOT NULL,
    mood_code TEXT NOT NULL,
    title_rewritten TEXT,
    content_rewritten TEXT,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (news_id) REFERENCES news(id),
    FOREIGN KEY (mood_code) REFERENCES moods(code),
    UNIQUE(news_id, mood_code)
);