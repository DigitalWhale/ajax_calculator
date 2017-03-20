<?php

$FOR_REG_NUM = '-?\d+\.?\d*';

function normalizeDeg($deg){
    if($deg > 0){
        while($deg>360){
            $deg -= 360;
        }
    }
    else{
        while($deg<-360){
            $deg += 360;
        }
    }
    return $deg;
}

function math($mat, $op){
    global $FOR_REG_NUM;
    $dig = array_values(preg_grep('/'.$FOR_REG_NUM.'/', explode($op, $mat)));
    switch($op){
        case '^':
            $res =  pow($dig[0],$dig[1]);
            break;
        case '*':
            $res = $dig[0]*$dig[1];
            break;
        case '/':
            $res = $dig[0]/$dig[1];
            break;
        case '+':
            $res = $dig[0]+$dig[1];
            break;
        case '-':
            $res = $dig[0]-$dig[1];
            break;
        default: $res = NAN;
    }
    return round($res, 5);
}

function mathFun($func, $dig){
    if($dig === ''){
        return NAN;
    }
    switch($func){
        case 'sqrt':
            $res = sqrt($dig);
            break;
        case 'cos':
            $res = cos(deg2rad($dig));
            break;
        case 'sin':
            $res = sin(deg2rad($dig));
            break;
        case 'tg':
            $dig = normalizeDeg($dig);
            if($dig != 90 && $dig != 270) {
                $res = tan(deg2rad($dig));
            }
            else{
                $res = NAN;
            }
            break;
        case 'ctg':
            $dig = normalizeDeg($dig);
            if($dig != 0 && $dig != 180 && $dig != 360) {
                $res = 1 / tan(deg2rad($dig));
            }else{
                $res = NAN;
            }
            break;
        default: $res = NAN;
    }
    return round($res, 5);
}

function findAr($mat){
    $req = findMath($mat, '-one');
    $req = findMath($req, '-two');
    $req = findMath($req, '-three');
    return $req;
}

function findFunc($req, $REG_FUN){
    $req = preg_replace_callback($REG_FUN, function($mat){
        $req = findAr($mat[0]);
        $dig = preg_replace('/[a-z]|\(|\)/', '', $req);
        $fun = preg_replace('/\.|,|\d*|\(|\)/', '', $req);
        $req = mathFun($fun, $dig);
        return $req;
    }, $req, -1, $count);
    return [$req, $count];
}

function findBrackets($req, $REG_BRACKETS){
    $req = preg_replace_callback($REG_BRACKETS, function($mat){
        $req = findAr($mat[0]);
        $req = preg_replace('/\(|\)/', '', $req);
        return $req;
    }, $req, -1, $count);
    return [$req, $count];
}

function findOp($req, $REG_OP){
    global $FOR_REG_NUM;
    $req = preg_replace_callback('/'.$FOR_REG_NUM.$REG_OP.$FOR_REG_NUM.'/', function($mat){
        global $FOR_REG_NUM;
        $REG_OP  = array_values(preg_grep('/'.$FOR_REG_NUM.'/', str_split($mat[0]), PREG_GREP_INVERT));
        return math($mat[0], $REG_OP[0] );
    }, $req, -1, $count);
    return [$req, $count];
}

function findMath($req, $op){
    $FOR_REG_ONE_OP = '\^';
    $FOR_REG_TWO_OP = '(\*|\/)';
    $FOR_REG_THREE_OP = '(\+|-)';
    $REG_BRACKETS = '/\([0-9\+\-\*\/\,\.]*\)/';
    $REG_FUN = '/((sin)|(cos)|(tg)|(ctg)|(sqrt))\([0-9\+\-\*\/\,\.]*\)/';
    switch($op){
        case '-one':
            $res = findOp($req, $FOR_REG_ONE_OP);
            break;
        case '-two':
            $res = findOp($req, $FOR_REG_TWO_OP);
            break;
        case '-three':
            $res = findOp($req, $FOR_REG_THREE_OP);
            break;
        case '-br':
            $res = findBrackets($req, $REG_BRACKETS);
            break;
        case '-f':
            $res = findFunc($req, $REG_FUN);
            break;
    }
    if($res[1] === 0){
        return $res[0];
    }
    $res[0] = findMath($res[0], $op);
    return $res[0];
}

function parseExpression($exp){
    if(!empty($exp)){
        $exp = preg_replace('/\s/', '', $exp);
        $exp = findMath($exp, '-f');
        $exp = findMath($exp, '-br');
        $exp = $req = findAr($exp);
        echo $exp;
    }
}

$req = $_POST["math"];
parseExpression($req);