<?php

namespace Base\Press\Controller\Admin\Crud;

use Base\Admin\Attribute\AdminAction;
use Base\Admin\Config\Action;
use Base\Admin\Config\Actions;
use Base\Admin\Controller\AbstractCrudController;
use Base\Admin\Filter\Filters;
use Base\Field\BooleanField;
use Base\Field\DateField;
use Base\Field\IdField;
use Base\Field\IntegerField;
use Base\Field\SelectField;
use Base\Field\TextareaField;
use Base\Field\TextField;
use Base\Press\Entity\Quote;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Filing what was said: the words, who said them and where, what about.
 * One button on each - "feature" - puts a quote among the few a home page
 * shows, or takes it back.
 */
class QuoteCrudController extends AbstractCrudController
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
        return Quote::class;
    }

    public static function getPreferredIcon(): ?string
    {
        return 'fa-solid fa-quote-left';
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('kind')->add('featured')->add('locale');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextareaField::new('text', '@press.admin.quote.text')->setHelp('@press.admin.quote.text_help');
        yield TextField::new('author', '@press.admin.quote.author')->setColumns(4)->setRequired(false);
        yield TextField::new('role', '@press.admin.quote.role')->setColumns(4)->setRequired(false)->hideOnIndex();
        yield TextField::new('source', '@press.admin.quote.source')->setColumns(4)->setRequired(false);
        yield TextField::new('url', '@press.admin.quote.url')->setColumns(6)->setRequired(false)->hideOnIndex();
        yield DateField::new('date', '@press.admin.quote.date')->setColumns(3)->setRequired(false);
        yield TextField::new('release', '@press.admin.quote.release')->setColumns(3)->setRequired(false)->hideOnIndex()->setHelp('@press.admin.quote.release_help');
        yield SelectField::new('kind', '@press.admin.quote.kind')->setColumns(3);
        yield SelectField::new('locale', '@press.admin.quote.locale')->setChoices(array_combine($this->locales, $this->locales))->setRequired(false)->setColumns(3)->setHelp('@press.admin.quote.locale_help');
        yield IntegerField::new('position', '@press.admin.quote.position')->setColumns(2);
        yield BooleanField::new('featured', '@press.admin.quote.featured')->setColumns(2);
        yield BooleanField::new('visible', '@press.admin.quote.visible')->setColumns(2);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        foreach ([Actions::PAGE_INDEX, Actions::PAGE_DETAIL] as $page) {
            $actions->add($page, Action::new('feature', '@press.admin.quote.action.feature', 'fa-solid fa-star')->linkToCrudAction('feature'));
        }

        return $actions;
    }

    #[AdminAction('/{entityId}/feature')]
    public function feature(string $entityId): Response
    {
        /** @var Quote $quote */
        $quote = $this->findEntity($entityId);
        $quote->toggleFeatured();
        $this->entityManager->flush();
        $this->addFlash('success', $quote->isFeatured() ? '@press.admin.quote.flash.featured' : '@press.admin.quote.flash.unfeatured');

        return $this->redirectToIndex();
    }
}
