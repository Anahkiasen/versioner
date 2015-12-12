<?php
namespace ComposerVersioner;

use League\HTMLToMarkdown\HtmlConverter;

class Changelog extends \Changelog\Parser
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var array
     */
    protected $releases = [];

    /**
     * Changelog constructor.
     *
     * @param string $file
     */
    public function __construct($file)
    {
        parent::__construct(file_get_contents($file));

        $this->file     = $file;
        $this->releases = $this->getReleases();
    }

    /**
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @param string $expected
     *
     * @return bool
     */
    public function hasRelease($expected)
    {
        return (bool) array_filter($this->releases, function ($release) use ($expected) {
            return $release['name'] === $expected;
        });
    }

    /**
     * @param array $release
     */
    public function addRelease(array $release)
    {
        $this->releases[] = $release;
    }

    /**
     * Save the new contents of the CHANGELOG
     * to the file
     *
     * @return string
     */
    public function save()
    {
        $html      = '# CHANGELOG';
        $converter = new HtmlConverter();
        foreach ($this->releases as $release) {
            if (!array_key_exists('changes', $release)) {
                continue;
            }

            $html .= PHP_EOL.PHP_EOL;
            $html .= '## ' .$release['name'].' - '.$release['date'];
            $html .= PHP_EOL.PHP_EOL;

            foreach ($release['changes'] as $section => $changes) {
                $html .= '### '.ucfirst($section);
                $html .= PHP_EOL.PHP_EOL;

                foreach ($changes as $change) {
                    $html .= '- '.$converter->convert($change);
                }
            }
        }

        file_put_contents($this->file, $html);

        return $html;
    }
}
