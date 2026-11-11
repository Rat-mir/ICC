-- 1. Каталог опубликованных мейкеров с автором и количеством элементов.
SELECT m.id,m.title,u.name AS creator,COUNT(a.id) AS assets_count
FROM makers m JOIN users u ON u.id=m.user_id LEFT JOIN assets a ON a.maker_id=m.id
WHERE m.status='approved' GROUP BY m.id,m.title,u.name ORDER BY m.id;
-- 2. Конфигурация персонажа и сохранённый порядок выбранных слоёв.
SELECT c.id,c.name,m.title,a.name AS asset,a.category,ac.layer_order
FROM characters c JOIN makers m ON m.id=c.maker_id
JOIN asset_character ac ON ac.character_id=c.id JOIN assets a ON a.id=ac.asset_id
WHERE c.id=1 ORDER BY ac.layer_order;
-- 3. Публикации и число уникальных реакций (без умножения на комментарии).
SELECT p.id,p.title,u.name AS author,COUNT(l.id) AS likes_count
FROM posts p JOIN users u ON u.id=p.user_id LEFT JOIN likes l ON l.post_id=p.id
GROUP BY p.id,p.title,u.name ORDER BY p.id;
-- 4. Возвращённые заявки с последним модератором.
SELECT m.id,m.title,creator.name AS creator,reviewer.name AS reviewer,m.review_comment
FROM makers m JOIN users creator ON creator.id=m.user_id LEFT JOIN users reviewer ON reviewer.id=m.reviewed_by
WHERE m.status='revision';
-- 5. Комментарии с публикацией и автором комментария.
SELECT c.id,p.title,u.name,c.body FROM comments c JOIN posts p ON p.id=c.post_id JOIN users u ON u.id=c.user_id ORDER BY c.id;
