<?php

$FOR_REG_NUM = '-?\d+\.?\d*';
$FOR_REG_ONE_OP = '\^';
$FOR_REG_TWO_OP = '(\*|\/)';
$FOR_REG_THREE_OP = '(\+|-)';
$REG_BRACKETS = '/\([0-9\+\-\*\/\,\.]*\)/';
$REG_FUN = '/((sin)|(cos)|(tg)|(ctg)|(sqrt))\([0-9\+\-\*\/\,\.]*\)/';

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

function math($mat, $OP){
    global $FOR_REG_NUM;
    $op = array_values(preg_grep('/'.$OP.'/', str_split($mat)));
    $dig = array_values(preg_grep('/'.$FOR_REG_NUM.'/', explode($op[0], $mat)));
    switch($op[0]){
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

function findFunc($req){
    global $REG_FUN;
    $req = preg_replace_callback($REG_FUN, function($mat){
        $req = findOp($mat[0], '-one');
        $req = findOp($req, '-two');
        $req = findOp($req, '-three');
        $dig = preg_replace('/[a-z]|\(|\)/', '', $req);
        $fun = preg_replace('/\.|,|\d*|\(|\)/', '', $req);
        $req = mathFun($fun, $dig);
        return $req;
    }, $req, -1, $count);
    return [$req, $count];
}

function findBrackets($req){
    global $REG_BRACKETS;
    $count = 0;
    $req = preg_replace_callback($REG_BRACKETS, function($mat){
        $req = findOp($mat[0], '-one');
        $req = findOp($req, '-two');
        $req = findOp($req, '-three');
        $req = preg_replace('/\(|\)/', '', $req);
        return $req;
    }, $req, -1, $count);
    return [$req, $count];
}

function findOneOp($req){
    global $FOR_REG_NUM;
    global $FOR_REG_ONE_OP;
    $req = preg_replace_callback('/'.$FOR_REG_NUM.$FOR_REG_ONE_OP.$FOR_REG_NUM.'/', function($mat){
        global $FOR_REG_ONE_OP;
        return math($mat[0], $FOR_REG_ONE_OP );
    }, $req, -1, $count);
    return [$req, $count];
}

function findTwoOp($req){
    global $FOR_REG_NUM;
    global $FOR_REG_TWO_OP;
    $req = preg_replace_callback('/'.$FOR_REG_NUM.$FOR_REG_TWO_OP.$FOR_REG_NUM.'/', function($mat){
        global $FOR_REG_TWO_OP;
        return math($mat[0], $FOR_REG_TWO_OP );
    }, $req, -1, $count);
    return [$req, $count];
}

function findThreeOp($req){
    global $FOR_REG_NUM;
    global $FOR_REG_THREE_OP;
    $req = preg_replace_callback('/'.$FOR_REG_NUM.$FOR_REG_THREE_OP.$FOR_REG_NUM.'/', function($mat){
        global $FOR_REG_THREE_OP;
        return math($mat[0], $FOR_REG_THREE_OP );
    }, $req, -1, $count);
    return [$req, $count];
}

function findOp($req, $op){
    switch($op){
        case '-one':
            $res = findOneOp($req);
            break;
        case '-two':
            $res = findTwoOp($req);
            break;
        case '-three':
            $res = findThreeOp($req);
            break;
        case '-br':
            $res = findBrackets($req);
            break;
        case '-f':
            $res = findFunc($req);
            break;
    }
    if($res[1] === 0){
        return $res[0];
    }
    $res[0] = findOp($res[0], $op);
    return $res[0];
}


$req = $_POST["math"];
if(!empty($req)){
    $req = preg_replace('/\s/', '', $req);
    $req = findOp($req, '-f');
    $req = findOp($req, '-br');
    $req = findOp($req, '-one');
    $req = findOp($req, '-two');
    $req = findOp($req, '-three');
    echo $req;
}