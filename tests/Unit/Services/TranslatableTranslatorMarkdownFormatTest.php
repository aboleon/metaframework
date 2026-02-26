<?php

declare(strict_types=1);

namespace DeepL {
    if (!class_exists(DeepLException::class)) {
        class DeepLException extends \Exception {}
    }

    if (!class_exists(TranslateTextOptions::class)) {
        class TranslateTextOptions
        {
            public const PRESERVE_FORMATTING = 'preserve_formatting';
            public const SPLIT_SENTENCES = 'split_sentences';
        }
    }

    if (!class_exists(Translator::class)) {
        class Translator
        {
            public function translateText($texts, ?string $sourceLang, string $targetLang, array $options = []): mixed
            {
                return (object) ['text' => (string) $texts];
            }
        }
    }
}

namespace Tests\Unit\Services {
    use DeepL\TranslateTextOptions;
    use DeepL\Translator;
    use MetaFramework\Services\TranslatableTranslator;
    use Tests\TestCase;

    class TranslatableTranslatorMarkdownFormatTest extends TestCase
    {
        public function test_translate_options_switch_for_markdown_like_multiline_content(): void
        {
            $service = new TranslatableTranslator(null);

            $markdownOptions = $this->invokeMethod($service, 'translateOptions', [
                "# Title\n\n- item\n| A | B |\n",
            ]);
            $plainOptions = $this->invokeMethod($service, 'translateOptions', [
                "A plain paragraph\nwith a wrapped line but no markdown syntax.",
            ]);

            $this->assertSame(true, $markdownOptions[TranslateTextOptions::PRESERVE_FORMATTING]);
            $this->assertSame('1', $markdownOptions[TranslateTextOptions::SPLIT_SENTENCES]);
            $this->assertSame('nonewlines', $plainOptions[TranslateTextOptions::SPLIT_SENTENCES]);
        }

        public function test_markdown_translation_preserves_line_breaks_and_table_layout(): void
        {
            $translator = $this->createMock(Translator::class);
            $translator
                ->expects($this->once())
                ->method('translateText')
                ->willReturnCallback(function ($text) {
                    if (is_array($text)) {
                        return array_map(static fn (string $item) => (object) ['text' => 'T[' . $item . ']'], $text);
                    }

                    return (object) ['text' => 'T[' . (string) $text . ']'];
                });

            $service = new TranslatableTranslator(null);
            $markdown = <<<MD
# Title

- Item one
| Col A | Col B |
| ----- | ----- |
| Val 1 | Val 2 |
MD;

            $translated = $this->invokeMethod($service, 'translateText', [
                $translator,
                $markdown,
                'FR',
                'BG',
            ]);

            $this->assertIsString($translated);
            $this->assertSame(substr_count($markdown, "\n"), substr_count((string) $translated, "\n"));
            $this->assertStringContainsString('# T[Title]', (string) $translated);
            $this->assertStringContainsString('- T[Item one]', (string) $translated);
            $this->assertStringContainsString('| T[Col A] | T[Col B] |', (string) $translated);
            $this->assertStringContainsString('| ----- | ----- |', (string) $translated);
            $this->assertStringContainsString('| T[Val 1] | T[Val 2] |', (string) $translated);
        }

        /**
         * @param  array<int, mixed>  $arguments
         */
        private function invokeMethod(object $instance, string $method, array $arguments = []): mixed
        {
            $reflection = new \ReflectionClass($instance);
            $reflectionMethod = $reflection->getMethod($method);

            return $reflectionMethod->invokeArgs($instance, $arguments);
        }
    }
}
