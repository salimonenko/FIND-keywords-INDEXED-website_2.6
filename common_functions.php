<?php
/* 1. Получаем метафоны */
function do_metaphone1($word, $metaphone_len){

    if(strlen($word) < $metaphone_len || preg_match('/[\d\+]/', $word)){ // Если слово короткое или содержит цифру или +, то будем искать точное соответствие (в транслите)
        return strtolower($word);
    }

    $word_metaphone = metaphone($word, $metaphone_len);

    if(strlen($word_metaphone) < ($metaphone_len-1)){ // Если функция metaphone дала слишком короткую строку-код
        return strtolower($word);
    }else{
        return strtolower($word_metaphone);
    }

}


/* 2. Транслит (обычный, НЕ псевдо) */
function translit1($value)
{
    $converter = array(
        'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
        'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
        'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
        'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
        'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
        'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
        'э' => 'e',    'ю' => 'yu',   'я' => 'ya',

        'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
        'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
        'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
        'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
        'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
        'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
        'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
    );

    $value = strtr($value, $converter);
    return $value;
}

/* 3. Определение кодировки */
function check_enc($text_html, $enc_Arr){ // В РНР 5.3 работает отлично. А в РНР 8.0 могут быть сбои, если входная строка будет в иной кодировке
// В массиве кодировок $enc_Arr ПЕРВОЙ должна идти utf-8
    $true_encoding = '';

    foreach ($enc_Arr as $encoding){
        if(mb_check_encoding($text_html, $encoding)){
            $true_encoding =  $encoding;
            break;
        }
    }

    return $true_encoding;
}

/* 4. Итоговое сообщение об ошибках */
// Функция окончательно проверяет ошибки
function check_ERRORS($mess){
// На всякий случай, окончательно делаем контроль ошибок
// *************    КОНТРОЛЬ ОШИБОК    (Начало)*****************************************
         if((error_get_last() != '') || (is_array(error_get_last()) && (error_get_last() != array()) )){
             print_r(error_get_last()); // Выводим клиенту, чтобы можно было посмотреть в ответах сервера

             if($mess){ // Это для ошибок, сообщения для которых вручную заданы в той или иной программе
                file_put_contents(PATH_FILE_NAMES_ERROR, $mess . PHP_EOL , FILE_APPEND);
             }

             save_ERROR_mes(); // Если была ошибка, сохраняем в файл также системное сообщение о ней в файл-лог ошибок

             return '<p class="error_mes">'. $mess. '</p>'. implode(PHP_EOL, error_get_last());
         }else{
             return '';
         }
// *************    /КОНТРОЛЬ ОШИБОК    (Конец)*****************************************
}

/* 5. Удаление НЕПУСТОГО каталога. РАБОТАЕТ ПЛОХО (может удалить не все каталоги, если их - много). Но, если сделать на итераторах, будет работать еще хуже (очень медленно)  */
function rrmdir($src, $DO_working_flag_FILE ) {

/* Проверяем, присутствует ли флаговый файл. Если да, то делаем следующую рекурсии. Если нет - прекращаем */
    if(!file_exists($DO_working_flag_FILE)){
        return 'stop';
    }

    $dir = opendir($src);

    while(false !== ($file = readdir($dir))) {
        if(($file != '.' ) && ( $file != '..' )) {

            $full = $src . '/' . $file;
            if(is_dir($full)) {

           /*     if(!file_exists($DO_working_flag_FILE)){
                    return false;
                }*/

                $rez = @rrmdir($full, $DO_working_flag_FILE);
                $mess = 'каталога ';
            }else{
                $rez = @unlink($full);
                $mess = 'файла ';
            }
            if(!$rez){
                echo 'Ошибка при удалении '. $mess. $full;
                print_r(error_get_last());
                die();
            }
        }
    }
    closedir($dir);

    if(!file_exists($DO_working_flag_FILE)){
        return 'stop';
    }

    rmdir($src);
return true;
}
/*
function rrmdir($src) {
    $dir = opendir($src);
    $src = realpath($src);


    $dirs_Arr = array_filter(scandir($src), function ($el) {
       if(is_file($el)){
           unlink($el);
            return false;
       }else{
           if($el == '.' || $el == '..'){
               return false;
           }

           return true;
       }
    });

    if(sizeof($dirs_Arr) > 0){
        $src =  realpath($src. '/'. $dirs_Arr[0]);
    }else{
        rmdir($src);
    }

}*/




/* 6. Функция определяет кодировку файла с перечнем файлов сайта files.txt и получает массив, состоящий из имен этих файлов */
function get_files_Arr($enc_Arr){
    $str = file_get_contents(PATH_FILE_NAMES_ALL_FILES);

$ENC_FILE_names_all_files = strtolower(check_enc($str, $enc_Arr));

    if(!$ENC_FILE_names_all_files){
        $mess = ' Не удалось определить кодировку файла '. PATH_FILE_NAMES_ALL_FILES;
        file_put_contents(PATH_FILE_NAMES_ERROR, $mess. ' '. date("d.m.Y - H:m:s"). PHP_EOL , FILE_APPEND);
    $mess .= '<p class="error_mes">'. $mess. '</p>';

return array(-1, $mess, null);
    }

$ALL_files_Arr_tmp = explode(PHP_EOL, $str); // Вместо функции file(), т.к. она, вроде бы, работает медленнее
unset($str);

$ALL_files_Arr = array(); // Массив относительных имен файлов
for($i=0; $i < sizeof($ALL_files_Arr_tmp); $i++){
    $elem = trim($ALL_files_Arr_tmp[$i]);

    if($elem){
        $pos = strpos($elem, '|') + 1;
        $key = substr($elem, $pos);
        $ALL_files_Arr[$key] = substr($elem, 0, $pos - 1); // Элемент массива вида: 3 => filename
    }
}
    if(isset($ALL_files_Arr['index'])){
        $max_UNIX_Arr = explode(':', $ALL_files_Arr['index']);
        $max_UNIX_saved = $max_UNIX_Arr[1];
        unset($ALL_files_Arr['index']);
    }else{
        $max_UNIX_saved = null;
    }

return array($ALL_files_Arr, $ENC_FILE_names_all_files, $max_UNIX_saved);
}

/* 7. Функция индексирует файл сайта (добавляя его индекс в индексный файл) ИЛИ добавляет признак присутствия слова в словаре в индексный файл  */
/**
 * @param $keyword_metaph
 * @param $body_Arr_k_count
 * @param $dic_word_SUFF
 * @param $file_name
 * @param $path_DIR_name_TMP
 * @param $number
 * @param $ALL_files_Arr
 * @param $JS_manage_mes
 * @return bool|string
 */
function LAST_met_2_index($keyword_metaph, $body_Arr_k_count, $dic_word_SUFF, $file_name, $path_DIR_name_TMP, $number, $ALL_files_Arr, $JS_manage_mes){

    $LAST_met_path_Arr = create_path_index_file($keyword_metaph, $path_DIR_name_TMP);
    $index_FILE = $LAST_met_path_Arr[0];
    $LAST_met_2 = $LAST_met_path_Arr[1];

            if($file_name === true && $number === ''){ // Если вставляется признак присутствия этого слова в файле-словаре ru.dic ("1" или суффикс, при его наличии)
                $delim = ':'. $dic_word_SUFF. '|';
                $to_SAVE = $LAST_met_2. $delim. ';'; // Строка вида ag:1|; или  ag:/HB|; (т.к. проводилось индексирование файла-СЛОВАРЯ)
            }else{ // Если индексируется содержимое файлов сайта
                $delim = '|;';
                $to_SAVE = $LAST_met_2. $delim. $number. '*'. $body_Arr_k_count.';'; // Строка вида ag|;560*3;
            }

        if(file_exists($index_FILE)){ // Если файл существует,
            if(is_writable($index_FILE)){ // Если файл доступен для записи, тогда проверяем, есть ли там такое индекс-число
//die('FFFFFFF');

                $index_FILE_str = file_get_contents($index_FILE);

                $reg = '~'. $LAST_met_2. '(([^\n\r]*;('. $number. '\*?(\d+)?);))~'; // Ищем подстроку типа ag...;560; или ag...;560*3;

                $flag_SAVE = false; // Флаг, нужно ли сохранять этот массив

               if(preg_match($reg, $index_FILE_str, $matches)){
//print_r($matches);
                    $index = $matches[3]; // Что-то типа  ;560*3;
// 1. Вначале удаляем индекс из соотв. строки
                    $index_FILE_str = str_replace(';'. $index. ';', ';', $index_FILE_str);
                }
                $index_FILE_Arr = explode("\n", $index_FILE_str);

                $index_FILE_Arr = array_filter($index_FILE_Arr, function ($el){
                    return $el != '';
                });
                for($z=0; $z < sizeof($index_FILE_Arr); $z++){ // По каждой строчке индексного файла

                    $elem = trim($index_FILE_Arr[$z]);
                    if(substr($elem, 0, 2) === $LAST_met_2){ // Если в начале элемента массива есть 2 символа типа ag

                        if($file_name === true && $number === ''){ // Если вставляется признак "1" - присутствия этого слова в файле-словаре ru.dic
                            if(substr($elem, 0, 5) !== $LAST_met_2. $delim){ // Если конец метафона есть, но признака присутствия еще нет
                                $index_FILE_Arr[$z] = trim(preg_replace('/^'. $LAST_met_2. '[^\\\n;]*/', $LAST_met_2. $delim, $index_FILE_Arr[$z]));

                                $to_SAVE = implode("\n", $index_FILE_Arr);
                                file_put_contents($index_FILE, $to_SAVE . "\n");

                            }
                            break; // Только в случае добавления признака "1" присутствия слова в файле-словаре (с учетом метафонизации)

                        }else{
                            if(strstr($elem, ';'. $number. ';') !== false){ // Если в том же элементе есть подстрока вида ;560; (лишняя проверка, т.к. выше эта подстрока была УДАЛЕНА) +++
                                break; //Если такая подстрока уже есть, значит, ее уже не нужно вставлять (если делается ИНДЕКСИРОВАНИЕ, а не вставка признака "1")
                            }else{ // Если еще нет, то добавляем. Будет что-то типа ag|;560*3;586*47;779*5; ... (или ag:1|;560*3;586*47;779*5; )
                                $index_FILE_Arr[$z] = $elem. $number. '*'. $body_Arr_k_count. ';';
                                $flag_SAVE = true;
                                    break;
                            }
                        }
                    }
                }

                if($z === sizeof($index_FILE_Arr)){
                // Если дошли досюда, т.е. НЕТ искомой подстроки типа ag
                    $index_FILE_Arr[] = $to_SAVE; // то добавляем в массив новый элемент
                        $flag_SAVE = true;
                }

                if($flag_SAVE){
                    $to_SAVE = implode("\n", $index_FILE_Arr);
                }

            }else{ // Если файл существует, но НЕ доступен для записи (значит, что-то пошло не так)
                $mess = 'Ошибка: не получилось записать число-индекс в файл '.$index_FILE. '. Т.к. этот файл недоступен для записи.';
                echo '<p class="error_mes">'. $mess. '</p>'. $JS_manage_mes;

                file_put_contents(PATH_FILE_NAMES_ERROR, $file_name. '|'. array_search($file_name, $ALL_files_Arr). '|'. $mess. ' '. date("d.m.Y - H:m:s"). PHP_EOL , FILE_APPEND);

return 'continue';
            }
        }else{ // Если файл не существует, то создаем его
            $flag_SAVE = true;
        }

        if($flag_SAVE){
            file_put_contents($index_FILE, $to_SAVE . "\n");
        }
// В итоге относительный путь к этому файлу будет примерно таким:  /metaphones/s/h/1.txt (для метафона shag). Буквы ag будут содержатьсяв одной из строк файла
// В этом файле будут содержаться индексные номера тех файлов (из files.txt), в которых метафон данного слова содержится хотя бы 1 раз

return true;
}

/* 8. Функция создает (или получает, если есть) путь к индексному файлу с именем вида $path_DIR_name_TMP/1.txt  */
function create_path_index_file($keyword_metaph, $path_DIR_name_TMP){
    for($j=0; $j < strlen($keyword_metaph)-2; $j++){ // По каждому отдельному символу данного слова, кроме предпоследнего и последнего символов
        $DIR_name_1 = substr($keyword_metaph, $j, 1); // Имена создаваемых каталогов будут состоять из 1 символа (a, b, c, d или т.п.)

        $path_DIR_name_TMP = $path_DIR_name_TMP. '/'. $DIR_name_1;
        if(!is_dir($path_DIR_name_TMP)){
            mkdir($path_DIR_name_TMP);
        }
    }
    $index_FILE = $path_DIR_name_TMP.'/1.txt';
    $LAST_met_2 = substr($keyword_metaph, $j, 2); // Последние 2 символа метафона

return array($index_FILE, $LAST_met_2);
}

/* 9. Функция проверяет корректность полученного логического выражения (перед последующей оценкой при помощи eval)  */
function check_keywords($keywords_Arr, $special_symb_Arr, $message_to_user, $bool_val){
    $bool_expression = implode('', array_map(function ($el) use ($special_symb_Arr, $bool_val){
    if(!in_array($el, $special_symb_Arr, true)){
        return $bool_val;
    }else{
        return $el;
    }
}, $keywords_Arr));

$rez_Arr = eval_keywords($bool_expression, $message_to_user);
return $rez_Arr;
}

/* 10. Функция оценивает логическое выражение и выдает результат: true или false  */
function eval_keywords($bool_expression, $message_to_user){
    $bool_expression_REZ = 0; // true, если есть совпадение с выражением для искомых искомых слов; false - если нет.

    $str_code = "\$bool_expression_REZ = ". $bool_expression;
    @eval($str_code. "|| 1". ";"); // Для проверки корректности выражения $str_code. Если оно верно, результат eval() даст заведомо 1 (true)

    if(!!$bool_expression_REZ){
        eval($str_code. ";"); // Если ошибки не было, получаем фактическое значение

        return array(null, !!$bool_expression_REZ);

    }else{ // Значит, возникла ошибка в выражении для eval()
        return array(-1, $message_to_user);
    }
}

/* 11. Функция получает массив индексов (из соответствующих индексных файлов), для каждого искомого (ключевого) слова из массива $keywords_Arr  */
function get_indexes_Arr($keywords_Arr, $special_symb_Arr, $path_DIR_name, $keywords_FALSE_Arr, $message_to_user){

    $keyword_indexes_num_Arr = array();
/* Массив вхождений каждого индекса:
 Array(
        Слово => (Array( Индекс => ЧислоВхождений))
      )
*/
    for($i=0; $i < sizeof($keywords_Arr); $i++){
        if(in_array($keywords_Arr[$i], $special_symb_Arr, true)){
            continue; // символы типа && || (  ), а также 1 - не ищем
        }

    // 1. Создаем путь к файлу 1.txt из каталога metaphones
        $str_to_DIRS = substr($keywords_Arr[$i], 0, -2);
        $str_to_FILE = substr($keywords_Arr[$i], -2);

        $path = realpath($path_DIR_name. '/' .implode('/', str_split($str_to_DIRS)). '/1.txt');

        if(!file_exists($path)){ // Если такого файла нет, значит, такого метафона нет в индексных файлах
            $keywords_FALSE_Arr[$i] = 0;
            continue;
        }else{ // Если такой файл есть
            $file_Arr = explode("\n", file_get_contents($path));

    // 2. Берем только тот элемент массива, который совпадает с 2-мя последними символами метафона
            $elem_Arr = array_filter($file_Arr, function ($el) use ($str_to_FILE){
                return substr($el, 0, 2) === $str_to_FILE;
            });

            if(sizeof($elem_Arr) > 1){
                die('<p class="error_mes">Похоже, ранее произошла ошибка индексирования: в файле '. $path. ' присутствуе БОЛЕЕ ОДНОЙ строки, начинающейся на "'. $str_to_FILE.'. А должно быть НЕ БОЛЕЕ одной строки. Следует исправить программу, при помощи которой ранее производилось индексирование файлов сайта.</p>');
            }elseif(sizeof($elem_Arr) === 0){ // Значит, нет индексов, соответствующих данному метафону
                $keywords_FALSE_Arr[$i] = 0;
            }else{ // Если в индексном файле ровно 1 такая строчка
                $elem_Arr = array_values($elem_Arr); // Чтобы начальный индекс массива стал равным 0
                $tmp = explode(';', $elem_Arr[0]);

                for($j=0; $j < sizeof($tmp); $j++){
                    $tmp1 = explode('*', $tmp[$j]);
                    $index = $tmp1[0];

                    if(sizeof($tmp1) === 1 && is_numeric($index)){
                        $keyword_indexes_num_Arr[$keywords_Arr[$i]][$index] = 1;
                    }elseif(sizeof($tmp1) > 1 && is_numeric($tmp1[1])){
                        $keyword_indexes_num_Arr[$keywords_Arr[$i]][$index] = $tmp1[1];
                    }
                }
            }


    // 3. И сразу проверяем, а вдруг при других метафонах, (пока) равных 0, логическое выражение уже будет равно true (это значит, что оно удовлетворяется и дальше можно не искать). Актуально для сложных логических выражений
            $rez_Arr = check_keywords($keywords_FALSE_Arr, $special_symb_Arr, $message_to_user, 0);

            if($rez_Arr[0] === -1){
                die('<p class="error_mes">Ошибка: выражение с искомыми словами составлено некорректно, функция eval() не может его оценить. Проблема возникла на слове '. $keywords_Arr[$i]. '</p>');
            }
            if($rez_Arr[1]){ // Если логическое выраж. уже равно true, значит, оно удовлетворяется и дальнейший поиск можно не делать (чтобы снизить время поиска)
                // Доделать todo +++
            }
        }

     }

return array($keyword_indexes_num_Arr);
}

/* 12. Функция сохраняем макс. метку времени UNIX в файл-перечень files.txt  */
function save_time_UNIX_MAX($str_UNIX_begin, $time_UNIX_i_MAX, $str_UNIX_end){
    $files = file_get_contents(PATH_FILE_NAMES_ALL_FILES);

// В 1-ю строку файла files.txt устанавливаем максимальную метку UNIX среди всех файлов, имена которых присутствуют в files.txt
    $files = preg_replace('~'. preg_quote($str_UNIX_begin, ':'). '(\d+)'. preg_quote($str_UNIX_end, '|'). '\s+~', $str_UNIX_begin. $time_UNIX_i_MAX. $str_UNIX_end. PHP_EOL, $files);

    file_put_contents(PATH_FILE_NAMES_ALL_FILES, $files);
}

// 13. Функция РЕГИСТРОНЕЗАВИСИМО проверяет, был ли передан КЛИЕНТУ заголовок (или готовый к отправке) вида $head_before: $header_after
function was_header($header_before, $header_after){
// Например:  Content-Type: text/event-stream
/* Возвращает 0, если такого заголовка нет,
              1, если такой заголовок есть,
             -1 в случае ошибки.
*/
    if(!$header_before || !$header_after){
return -1;
    }

    $header_reg = '~'. preg_quote($header_before). '\s*\:\s*'. preg_quote($header_after). '~i'; // Если вдруг были отправлены заголовки не строго по стандарту
    $headers_Arr = headers_list();

    $flag_exists = false;
    for($i=0; $i < sizeof($headers_Arr); $i++){

        $flag_exists = preg_match($header_reg, $headers_Arr[$i]);
        if($flag_exists === false){
return -1;

        }elseif($flag_exists){
return $flag_exists;
        }
    }

return $flag_exists;
}

// 14. Функция сохраняет сообщение о последней ошибке в файл-лог ошибок
function save_ERROR_mes(){
    file_put_contents(PATH_FILE_NAMES_ERROR, 'Ошибка - '. date("d.m.Y - H:m:s") . PHP_EOL , FILE_APPEND);

    array_map(function ($el) { // Сохраняем в файл также системное сообщение об ошибке построчно
        $str_to_out = array_search($el, error_get_last()). ' => '. $el;
        file_put_contents(PATH_FILE_NAMES_ERROR, $str_to_out. PHP_EOL , FILE_APPEND);
    }, error_get_last());
}


// 15. После окончания работы в случае ошибки сообщаем об этом (актуально, если будет Fatal error)
register_shutdown_function(function () {
// 1. При использовании SSE (событий сервера). Т.е. если был (будет) отправлен заголовок text/event-stream
    $text_event_stream = was_header('Content-Type', 'text/event-stream');

    if($text_event_stream === 1){
        require_once __DIR__ . '/sendMsg.php'; // Вывод результатов событий сервера
        if(check_ERRORS('')){ // Если были ошибки
            sendMsg(time(), '<p class="error_mes" style="display: block">Ошибка: </p>', false);

            array_map(function ($el) { // Выводим клиенту сообщение об ошибке при использовании SSE
                $str_to_out = array_search($el, error_get_last()). ' => '. $el;
                sendMsg(time(), '<p class="error_mes" style="display: block">'. $str_to_out. '</p>', false);
            }, error_get_last());

        }
    // Если невозможно определить, работают ли SSE (установлен ли заголовок Content-Type: text/event-stream)
    }elseif($text_event_stream === -1){
        save_ERROR_mes();  // Сохраняем в файл также системное сообщение об ошибке в файл-лог ошибок
        echo 'Ошибка в функции was_header() - '. __FUNCTION__ . ', стр.'. __LINE__ ; // Выводим клиенту хоть какое-то сообщение об ошибке
    }

});


/* 16. Функции делают псевдологические операции с числами. Могут складывать их или находить значение специальной функции (через eval).
    Для number1 || number2  ->     number_OR_F(number1, number2)
    Для number1 && number2  ->     number_I_F(number1, number2)
*/
// 16.1. Функция для выполнения ранжирования для псевдологического условия И ( && )
function number_I_F($number1, $number2){
/*                              |$number1 - $number2|
 *   min($number1, $number2) + -----------------------
 *                             max($number1, $number2)
 */
    if(!is_numeric($number1) || !is_numeric($number2)){
        return -1;
    }
    if(!$number1 || !$number2){
        return 0;
    }

return min($number1, $number2) + abs($number1 - $number2) / max($number1, $number2);
}
// 16.2. Функция суммирования. Определяет ранг для операции ИЛИ ( || )
function number_OR_F($number1, $number2){
    if(!is_numeric($number1) || !is_numeric($number2)){
        return -1;
    }

return $number1 + $number2;
}

// 17. Функция ищет пересечение массивов индексов. Для найденного пересечения определяет ранг каждого индекса по формуле, задаваемой при помощи ф-ции  number_I_F()
function array_inters__ect($b1, $b2){
/* $b1 = Array(              $b2 = Array(
               [33] => 1                 [33] => 6
               [99] => 3                 [76] => 1
               [103] => 1                [93] => 2
               [108] => 4                [108] => 2
              )                          [127] => 1
                                         [216] => 2
                                        )
*/
    $range_Arr = array();

    $indexes_Arr = array_keys(array_intersect_key($b1, $b2)); // Уникальные (содержащиеся в пересечении) ключи массивов
// Собираем ранги для уникальных ключей
    for($i=0; $i < sizeof($indexes_Arr); $i++){ // По каждому уникальному индексу, присутствующему в пересечении
        $ind1 = $b1[$indexes_Arr[$i]];
        $ind2 = $b2[$indexes_Arr[$i]];
        $range_Arr[$indexes_Arr[$i]] = number_I_F($ind1, $ind2);
    }
    arsort($range_Arr); // Элементы с высокими рангами будет первыми

return $range_Arr;
}

// 18. Функция ищет объединение массивов индексов. Для найденного объединения определяет ранг каждого индекса по формуле, задаваемой при помощи ф-ции  number_OR_F()
function array_mer__ge($b1, $b2){
/* $b1 = Array(              $b2 = Array(
               [33] => 1                 [33] => 6
               [99] => 3                 [76] => 1
               [103] => 1                [93] => 2
               [108] => 4                [108] => 2
              )                          [127] => 1
                                         [216] => 2
                                        )
*/
    $range_Arr = array();

    $indexes = array_keys($b1);
    $indexes = array_values(array_merge(array_keys($b2), $indexes));
// Собираем ранги для уникальных ключей
    for($i=0; $i < sizeof($indexes); $i++){ // По каждому уникальному индексу, присутствующему в объединении

        if(isset($b1[$indexes[$i]])){
            $range_Arr[$indexes[$i]] = $b1[$indexes[$i]];
        }
        if(isset($b2[$indexes[$i]])){
            $range_Arr[$indexes[$i]] = $b2[$indexes[$i]];;
        }
        if(isset($b1[$indexes[$i]]) && isset($b2[$indexes[$i]])){
            $range_Arr[$indexes[$i]] = number_OR_F($b1[$indexes[$i]], $b2[$indexes[$i]]);
        }
    }
    arsort($range_Arr); // Элементы с высокими рангами будет первыми

return $range_Arr;
}
