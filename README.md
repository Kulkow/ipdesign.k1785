Модуль битрикс выполнение задания
1а), 2а), 3
INSTALLATION
------------
cd path_server/bitrix/modules
git clone https://github.com/Kulkow/ipdesign.k1785.git ipdesign.k1785

Переходим в панель администрирования  http://site.ru/bitrix/admin/partner_modules.php?lang=ru
Устанавливаем модуль

1.а) послеустановки пояляется контрол для скидок (правила работы с корзиной)
http://bizness/bitrix/admin/sale_discount.php?lang=ru
Примерно, так http://site.ru/bitrix/modules/ipdesign.k1785/images/basket_discount.png
и так http://site.ru/bitrix/modules/ipdesign.k1785/images/basket_discount2.png

2а) Выполнения скрипта ssh:// php bitrix/php_interface/agents/itsender.php (создается почтовый шаблон при установке модуля)

3) При создании модуля добавляется компонент с шаблоном верстки it.rating можно добавить на страницу
