<?php
namespace Zk2\Bundle\UsefulBundle\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * The class implements a time zone conversion (CONVERT_TZ) function, clear Doctrine
 */
class ZkConvertTZ extends FunctionNode
{
    /**
     * {@inheritdoc}
     */
    private $expr = array();

    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        
        $this->expr[] = $parser->ArithmeticExpression();
 
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
        
    }

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf("CONVERT_TZ(%s, 'UTC', 'Europe/Kiev')",
            $sqlWalker->walkArithmeticExpression($this->expr[0])
        );
    }

} 