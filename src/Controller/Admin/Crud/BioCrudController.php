<?php

namespace Base\Press\Controller\Admin\Crud;

use Base\Admin\Controller\AbstractCrudController;
use Base\Admin\Filter\Filters;
use Base\Field\DateTimeField;
use Base\Field\IdField;
use Base\Field\IntegerField;
use Base\Field\SelectField;
use Base\Field\TextareaField;
use Base\Press\Entity\Bio;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Writing the biographies: one per language and per length. Plain text, a
 * blank line between two paragraphs; the words are counted as they are
 * typed, against what the length is meant to hold (100, 250).
 */
class BioCrudController extends AbstractCrudController
{
    /** @var list<string> */
    private array $locales = ['en', 'fr', 'de'];

    /** The site's languages (framework.enabled_locales), when it declares them. */
    #[Required]
    public function setPressLocales(#[Autowire('%kernel.enabled_locales%')] array $locales = []): void
    {
        $this->locales = $locales ?: $this->locales;
    }

    public static function getEntityFqcn(): string
    {
        return Bio::class;
    }

    public static function getPreferredIcon(): ?string
    {
        return 'fa-solid fa-address-card';
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('locale')->add('length');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield SelectField::new('locale', '@press.admin.bio.locale')->setChoices(array_combine($this->locales, $this->locales))->setColumns(3);
        yield SelectField::new('length', '@press.admin.bio.length')->setColumns(3)->setHelp('@press.admin.bio.length_help');
        yield IntegerField::new('wordCount', '@press.admin.bio.words')->onlyOnIndex();
        yield DateTimeField::new('updatedAt', '@press.admin.bio.updated_at')->onlyOnIndex();
        yield TextareaField::new('content', '@press.admin.bio.content')->setNumOfRows(18)->hideOnIndex()
            ->setHelp('@press.admin.bio.content_help')
            ->addHtmlContentsToBody(self::WORD_COUNTER);
    }

    /**
     * The count under the text, live: the same words Bio::countWords() counts,
     * against the 100 or 250 of the length chosen above.
     */
    private const WORD_COUNTER = <<<'HTML'
        <script>
        (function () {
            var text = document.querySelector('textarea[name$="[content]"]');
            if (!text || text.dataset.pressCounted) return;
            text.dataset.pressCounted = '1';
            var length = document.querySelector('select[name$="[length]"]'), targets = {short: 100, medium: 250};
            var out = document.createElement('p');
            out.className = 'press-word-count'; out.style.cssText = 'margin:.35rem 0 0;font-size:.85rem;opacity:.8';
            text.insertAdjacentElement('afterend', out);
            function count() {
                var words = (text.value.match(/[\p{L}\p{N}][\p{L}\p{N}\p{M}'’\-.]*/gu) || []).length;
                var target = length ? targets[length.value] : null;
                out.textContent = words + (target ? ' / ' + target : '');
                out.style.color = target && words > target * 1.1 ? '#d9404e' : '';
            }
            text.addEventListener('input', count);
            if (length) length.addEventListener('change', count);
            count();
        })();
        </script>
        HTML;
}
