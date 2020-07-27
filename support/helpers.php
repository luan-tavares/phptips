<?php

/**
 * ####################
 * ###   VALIDATE   ###
 * ####################
 */


/**
 * @param string $email
 * @return bool
 */
function is_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * @param string $password
 * @return bool
 */
function is_passwd(string $password): bool
{
    if (password_get_info($password)['algo']) {
        return true;
    }

    return (
            mb_strlen($password) >= CONFIG_PASSWORD_MIN_LENGTH &&
            mb_strlen($password) <= CONFIG_PASSWORD_MAX_LENGTH ? true : false
        );
}

/**
 * @param string $password
 * @return string
 */
function passwd(string $password): string
{
    return password_hash($password, CONFIG_PASSWORD_ALGO, CONFIG_PASSWORD_OPTION);
}

/**
 * @param string $password
 * @param string $hash
 * @return bool
 */
function passwd_verify(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * @param string $hash
 * @return bool
 */
function passwd_rehash(string $hash): bool
{
    return password_needs_rehash($hash, CONFIG_PASSWORD_ALGO, CONFIG_PASSWORD_OPTION);
}

/**
 * @return string
 */
function csrf_input(): string
{
    session()->csrf();
    return "<input type='hidden' name='csrf' value='" . (session()->csrf_token ?? "") . "'/>";
}

/**
 * @param $request
 * @return bool
 */
function csrf_verify($request): bool
{
    if (empty(session()->csrf_token) || empty($request['csrf']) || $request['csrf'] != session()->csrf_token) {
        return false;
    }
    return true;
}


/**
 * ##################
 * ###   STRING   ###
 * ##################
 */

 /**
 * @param string $string
 * @return string
 */
function str_replace_recursive($search, $replace, $string): string
{
    if (is_int(mb_strpos($string, $search))) {
        $string = str_replace($search, $replace, $string);
        return str_replace_recursive($search, $replace, $string);
    }

    return $string;
}


/**
 * @param string $string
 * @return string
 */
function str_slug(string $string): string
{
    $string = filter_var(mb_strtolower($string), FILTER_SANITIZE_STRIPPED);
    $formats = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
    $replace = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';
    $slug = str_replace_recursive(
        "--",
        "-",
        str_replace(
            " ",
            "-",
            trim(strtr(utf8_decode($string), utf8_decode($formats), $replace))
        )
    );
    return $slug;
}

/**
 * @param string $string
 * @return string
 */
function str_studly_case(string $string): string
{
    $string = str_slug($string);
    $studlyCase = str_replace(
        " ",
        "",
        mb_convert_case(str_replace("-", " ", $string), MB_CASE_TITLE)
    );

    return $studlyCase;
}

/**
 * @param string $string
 * @return string
 */
function str_camel_case(string $string): string
{
    return lcfirst(str_studly_case($string));
}

/**
 * @param string $string
 * @return string
 */
function str_title(string $string): string
{
    return mb_convert_case(filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS), MB_CASE_TITLE);
}

/**
 * @param string $string
 * @param int $limit
 * @param string $pointer
 * @return string
 */
function str_limit_words(string $string, int $limit, string $pointer = "..."): string
{
    $string = trim(filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS));
    $arrWords = explode(" ", $string);
    $numWords = count($arrWords);

    if ($numWords < $limit) {
        return $string;
    }

    $words = implode(" ", array_slice($arrWords, 0, $limit));
    return "{$words}{$pointer}";
}

/**
 * @param string $string
 * @param int $limit
 * @param string $pointer
 * @return string
 */
function str_limit_chars(string $string, int $limit, string $pointer = "..."): string
{
    $string = trim(filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS));
    if (mb_strlen($string) <= $limit) {
        return $string;
    }

    $chars = mb_substr($string, 0, mb_strrpos(mb_substr($string, 0, $limit), " "));
    return "{$chars}{$pointer}";
}

/**
 * ##################
 * ###   STRING   ###
 * ##################
 */


/**
 * @param string $path
 * @return string
 */
function url(string $path): string
{
    return CONFIG_URL_BASE . "/" . ($path[0] == "/" ? mb_substr($path, 1) : $path);
}

/**
 * @param string $url
 */
function redirect(string $url): void
{
    header("HTTP/1.1 302 Redirect");
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        header("Location: {$url}");
        exit;
    }

    $location = url($url);
    header("Location: {$location}");
    exit;
}


/**
 * ################
 * ###   CORE   ###
 * ################
 */


/**
 * @return PDO
 */
function db(): PDO
{
    return \Core\Database\Connect::instance();
}

/**
 * @return \Core\Message\Message
 */
function message(): \Core\Message\Message
{
    return new \Core\Message\Message();
}

/**
 * @return \Core\Session\Session
 */
if (!function_exists('session')) {
    function session(): \Core\Session\Session
    {
        return (\Core\Session\Session::instance());
    }
}


/**
 * #################
 * ###   MODEL   ###
 * #################
 */


/**
 * @return \App\Models\User
 */
function user(): \App\Models\User
{
    return new \App\Models\User;
}


/**
 * #################
 * ###   VIEWS   ###
 * #################
 */
function asset(string $path)
{
    return CONFIG_URL_BASE ."/assets/{$path}";
}
