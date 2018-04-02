<?php

use PHPUnit\Framework\TestCase;

class PhpRendererTest extends TestCase
{
    public function testRenderer()
    {
        $renderer = new \Chiron\Views\PhpRenderer("tests/");

        $response = $renderer->render("testTemplate.php", array("hello" => "Hi"));

        $this->assertEquals("Hi", $response);
    }

    public function testRenderConstructor()
    {
        $renderer = new \Chiron\Views\PhpRenderer("tests");

        $response = $renderer->render("testTemplate.php", array("hello" => "Hi"));

        $this->assertEquals("Hi", $response);
    }

    public function testAttributeMerging()
    {
        $renderer = new \Chiron\Views\PhpRenderer("tests/", [
            "hello" => "Hello"
        ]);

        $response = $renderer->render("testTemplate.php", [
            "hello" => "Hi"
        ]);
        $this->assertEquals("Hi", $response);
    }

    public function testExceptionInTemplate()
    {
        $renderer = new \Chiron\Views\PhpRenderer("tests/");

        try {
            $response = $renderer->render("testException.php");
        } catch (Throwable $t) { // PHP 7+
            // Simulates an error template
            ob_end_clean();
            $response = $renderer->render("testTemplate.php", [
                "hello" => "Hi"
            ]);
        }

        $this->assertEquals("Hi", $response);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testTemplateNotFound()
    {
        $renderer = new \Chiron\Views\PhpRenderer("tests/");

        $renderer->render("adfadftestTemplate.php", []);
    }
}