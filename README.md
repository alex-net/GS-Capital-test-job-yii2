# Тестовое задание. для компании "GS Capital"

Необходимо разработать скандинавский аукцион.

## Что нужно сделать:
0.  Выбрать и реализовать БД.
0. Выбрать и применить дополнительные технологии (бд, кэши, очереди и тд).
0. Выбрать реализовать/частично реализовать основной функционал, насколько это возможно.

## Обязательные требования:
1. Необходимо описать как установить проект на локалке. Лучше использовать Docker.
2. Нужно посчитать количество времени затраченное на решение задачи
3. Основное хранилище - MySQL
4. Основный язык - PHP (ООП) или с любым PHP фреймворком

## Что вам не нужно делать:
1. Не делать дизайн, просто выполнить саму задачу.
2. Сложные механизмы общения фронта и бэка не использовать, делать через http запрос.
3. Не прорабатывать слишком глубоко - это же все-таки тестовое задание! :) Лучше об этом просто рассказать в ходе технического интервью.
4. Админку/личный кабинет игрока не делать, все данные добавлять напрямую через БД
5. Авторизация не нужна, не нужны также и личные кабинеты и т.д.

---------------

## Запуск прокта

- В корне скачанного репозитария выполнить команду запуска Docker compose с настройками из файла [docker-compose.yml](./docker-compose.yml) `` docker compose up -d``
- зайти в контейнер php. используя команду ``docker exec -ti [php-name-container] sh``, где *[php-name-container]* - имя контенера которое можно взять из списка получившегося после выполнения команды ``docker ps ``
- в контейнере php выполнить команды
    - `` composer i`` - *установка пакетов php*
    - `` ./yii migrate`` - *применение миграций*
- перейти в браузере по адресу [127.0.0.1:8081/site/login](http://127.0.0.1:8081/site/login) и авторизоваться с любым доступным на странице логином/паролем
- перейти по адресу [/lots/index](http://127.0.0.1:8081/lots/index) и добавить несколько позиций товаров для проведения аукциона. Далее любые понравившиеся лоты переветсти в состояние "Торгуется" (последний столбец "состояние лота")
- далее все доступные для торговли лоты будут видны по адресу [/lots/plaig](http://127.0.0.1:8081/lots/plaig).

## Описание страницы для проведения торгов.
Страница предствалена списком лотов как доступных для торговли так и лотов, торговля для  которых завершина. У каждого элемента имеется наименование, ценник, и индикатор времени, который показывает время до окончания торгов. Ценник может быть зелёного (торги по лоту открыты) и красного (торги не доступны) цветов. Также по ценнику можно кликнуть мышью и поднять ставку после чего стоимость лота увеличится на фиксированное для этого лота значение, а индикатор снова вырастит до максимума.


