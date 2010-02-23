# Убираем признак получения валюты
UPDATE currency c SET cur_uses=0 WHERE cur_id IN (8);
# Устанавливаем новые курсы, получаемые с центробанка
UPDATE currency c SET cur_uses=1 WHERE cur_id IN (24,30,53,71,72,81,83,87,90,101,133,152,163,165,169,170,171,172,173);