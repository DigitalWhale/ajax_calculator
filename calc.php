<?php

$FOR_REG_NUM = '-?\d+\.?\d*';
$FOR_REG_ONE_OP = '\^';
$FOR_REG_TWO_OP = '(\*|\/)';
$FOR_REG_THREE_OP = '(\+|-)';
$REG_BRACKETS = '/\(.*\)/';
$REG_FUN = '/((sin)|(cos)|(tan)|(ctan)|(sqrt))\(.*\)/';

function math($mat, $OP){
    global $FOR_REG_NUM;
    $op = array_values(preg_grep('/'.$OP.'/', str_split($mat)));
    $dig = array_values(preg_grep('/'.$FOR_REG_NUM.'/', explode($op[0], $mat)));
    switch($op[0]){
        case '^':
            return pow($dig[0],$dig[1]);
        case '*':
            return $dig[0]*$dig[1];
            break;
        case '/':
            return $dig[0]/$dig[1];
            break;
        case '+':
            return $dig[0]+$dig[1];
            break;
        case '-':
            return $dig[0]-$dig[1];
            break;
    }
}

function mathFun($func, $dig){
    switch($func){
        case 'sqrt':
            return sqrt($dig);
        case 'cos':
            return cos(deg2rad($dig));
            break;
        case 'sin':
            return sin(deg2rad($dig));
            break;
        case 'tan':
            return tan(deg2rad($dig));
            break;
        case 'ctan':
            return 1/tan(deg2rad($dig));
            break;
    }
}

function findFunc($req){
    global $REG_FUN;
    $count = 0;
    $req = preg_replace_callback($REG_FUN, function($mat){
        $req = findOneOp($mat[0]);
        $req = findTwoOp($req);
        $req = findThreeOp($req);
        $dig = preg_replace('/[a-z]|\(|\)/', '', $req);
        $fun = preg_replace('/\d*|\(|\)/', '', $req);
        $req = mathFun($fun, $dig);
        return $req;
    }, $req, -1, $count);
    if($count === 0){
        return $req;
    }
    $req = findFunc($req);
    return $req;
}

function findBrackets($req){
    global $REG_BRACKETS;
    $count = 0;
    $req = preg_replace_callback($REG_BRACKETS, function($mat){
        $req = findOneOp($mat[0]);
        $req = findTwoOp($req);
        $req = findThreeOp($req);
        $req = preg_replace('/\(|\)/', '', $req);
        return $req;
    }, $req, -1, $count);
    if($count === 0){
        return $req;
    }
    $req = findBrackets($req);
    return $req;
}


function findOneOp($req){
    global $FOR_REG_NUM;
    global $FOR_REG_ONE_OP;
    $count = 0;
    $req = preg_replace_callback('/'.$FOR_REG_NUM.$FOR_REG_ONE_OP.$FOR_REG_NUM.'/', function($mat){
        global $FOR_REG_ONE_OP;
        return math($mat[0], $FOR_REG_ONE_OP );
    }, $req, -1, $count);
    if($count === 0){
        return $req;
    }
    $req = findOneOp($req);
    return $req;
}

function findTwoOp($req){
    global $FOR_REG_NUM;
    global $FOR_REG_TWO_OP;
    $count = 0;
    $req = preg_replace_callback('/'.$FOR_REG_NUM.$FOR_REG_TWO_OP.$FOR_REG_NUM.'/', function($mat){
        global $FOR_REG_TWO_OP;
        return math($mat[0], $FOR_REG_TWO_OP );
    }, $req, -1, $count);
    if($count === 0){
        return $req;
    }
    $req = findTwoOp($req);
    return $req;
}

function findThreeOp($req){
    global $FOR_REG_NUM;
    global $FOR_REG_THREE_OP;
    $count = 0;
    $req = preg_replace_callback('/'.$FOR_REG_NUM.$FOR_REG_THREE_OP.$FOR_REG_NUM.'/', function($mat){
        global $FOR_REG_THREE_OP;
        return math($mat[0], $FOR_REG_THREE_OP );
    }, $req, -1, $count);
    if($count === 0){
        return $req;
    }
    $req = findThreeOp($req);
    return $req;
}


$req = $_POST["math"];
if(!empty($req)){
//    $req = preg_replace_callback($REG_BRACKETS, function($mat){
//        return "LOL";
//    }, $req, -1, $count);

    $req = findFunc($req);
    $req = findBrackets($req);
    $req = findOneOp($req);
    $req = findTwoOp($req);
    $req = findThreeOp($req);
    echo $req;
}