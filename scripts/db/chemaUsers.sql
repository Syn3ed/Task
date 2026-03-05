-- пример схемы таблицы Users, где каждое поле имеет макс значение по символам, уникальность и не может быть пустым
Users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(25) NOT NULL,
    login       VARCHAR(25)  UNIQUE NOT NULL,     
    phone       VARCHAR(11)  UNIQUE NOT NULL,
    email       VARCHAR(30) UNIQUE NOT NULL,
    password    VARCHAR(100) NOT NULL,
);
