# FIND-keywords-INDEXED-website_2.6
This version adds search results ranking. Site files containing more keyword matches will appear higher (first) in search results, taking into account the search logical expression. This includes fuzzy search.

The search engine requires at least 380 MB of free disk space. When indexing the Russian dictionary, a "metaphones" directory will be created, containing approximately 150,000 subdirectories and files. Subsequently, during the indexing process for the site file contents, the corresponding indexes will be written to this directory. For a large number of site files (10,000 to 50,000), the space occupied by the search system may increase slightly.

Fast FUZZY search for searched (keywords) in site files. Indexing. Improved version of FIND-keywords-INDEXED-website.

В этой версии добавлено ранжирование результатов поиска. Файла сайта, содержащие больше вхождений ключевых слов, будут выше (первыми) в результатах поиска - с учетом логического выражения для поиска. В том числе, для нечеткого поиска.

Для работы поисковой системы необходимо от 380 МБ свободного дискового пространства. При индексировании словаря русского языка будет создан каталог metaphones, в нем будет создано около 150 тысяч вложенных каталогов и файлов. Потом, в процессе индексирования содержимого файлов сайта, туда будут записываться соответствующие индексы. Для большого числа файлов сайта (от 10...50 тысяч) объем, занимаемый системой поиска, может немного увеличиться. 

Быстрый НЕЧЕТКИЙ поиск искомых (ключевых) слов по файлам сайта. Индексирование. Улучшенная версия FIND-keywords-INDEXED-website

Вероятно, эту систему нет смысла использовать при малом числе индексируемых файлов (менее 5...10 тысяч). Т.к., несмотря на очень быстрый поиск ключевых слов, ее индексы занимают достаточно много места на жестком диске (около 400 МБ, что примерно в 5...10 раз больше, чем объем самих индексируемых файлов, в которых производится поиск). Для сайтов с малым числом файлов целесообразно использовать более ранние версии (2.5 и т.д.). 
