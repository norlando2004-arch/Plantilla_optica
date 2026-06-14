<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Models\Promocion;
use App\Models\Producto;
use App\Models\Resena;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResenaController;
use App\Services\LandingBenefitsContent;
use App\Services\LandingBenefitStripContent;
use App\Services\LandingCategoryPhotosContent;
use App\Services\LandingCategoriesContent;
use App\Services\LandingContactContent;
use App\Services\LandingFaqContent;
use App\Services\LandingEssentialBenefitsContent;
use App\Services\LandingHighlightsContent;
use App\Services\LandingHeroCarouselContent;
use App\Services\LandingSecondaryCarouselContent;
use App\Services\LandingHowItWorksContent;
use App\Services\LandingIntroContent;
use App\Services\LandingLocationContent;
use App\Services\LandingNewsletterContent;
use App\Services\LandingFooterContent;
use App\Services\LandingPromoBannersContent;
use App\Services\LandingQuickGuideContent;
use App\Services\LandingServicesContent;
use App\Services\LandingWhatsappContent;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HeroBannerController;
use App\Http\Controllers\Admin\SecondaryBannerController;
use App\Http\Controllers\Admin\GafasMujeresController as AdminGafasMujeresController;
use App\Http\Controllers\Admin\GafasHombreController as AdminGafasHombreController;
use App\Http\Controllers\Admin\GafasNinasController as AdminGafasNinasController;
use App\Http\Controllers\Admin\GafasNinosController as AdminGafasNinosController;
use App\Http\Controllers\Admin\GafasPolarizadasController as AdminGafasPolarizadasController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\InformacionController;
use App\Http\Controllers\GafasMujeresController;
use App\Http\Controllers\GafasNinasController;
use App\Http\Controllers\GafasNinosController;
use App\Http\Controllers\GafasPolarizadasController;
use App\Http\Controllers\GafasHombreController;
use App\Http\Controllers\GafasController;
use App\Http\Controllers\GafaController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\PagosWebhookController;
use App\Http\Controllers\PagosRetornoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PerfilClienteController;
use App\Http\Controllers\ContentBlockAssetController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\SeguimientoPedidoController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\TestMailController;

Route::view('/arreglos-tecnicos', 'arreglos-tecnicos')->name('arreglos.tecnicos');

$landingHandler = function () {
	$startedAt = microtime(true);
	$timeBudgetSeconds = 8.0;
	$hasBudget = static fn (): bool => (microtime(true) - $startedAt) < $timeBudgetSeconds;
	$safeLoad = static function (callable $loader, mixed $fallback) use ($hasBudget) {
		if (!$hasBudget()) {
			return $fallback;
		}

		try {
			return $loader();
		} catch (\Throwable $e) {
			return $fallback;
		}
	};

	// En producción (Render), evita que una consulta lenta tumbe el landing completo.
	try {
		$conn = DB::connection();
		$driver = (string) $conn->getDriverName();

		if ($driver === 'pgsql') {
			$conn->statement('SET statement_timeout TO 1500');
		} elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
			$conn->statement('SET SESSION MAX_EXECUTION_TIME=1500');
		}
	} catch (\Throwable $e) {
		// noop
	}

	$welcomePromo = null;
	$heroBanners = collect();
	$secondaryBanners = collect();
	$topProducts = collect();
	$editorialMostPurchased = null;
	$editorialTopVisited = null;
	$editorialNewest = null;
	$reviewsAvg = 0;
	$reviewsCount = 0;
	$latestReviews = collect();
	$verifiedBuyerUserIds = [];

	$gafasTipos = ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'];

	if ($hasBudget()) {
	try {
		$welcomePromo = Promocion::query()
			->where('tipo', 'bienvenida')
			->where('esta_activa', true)
			->where(function ($q) {
				$q->whereNull('empieza_en')->orWhere('empieza_en', '<=', now());
			})
			->where(function ($q) {
				$q->whereNull('termina_en')->orWhere('termina_en', '>=', now());
			})
			->orderBy('orden')
			->latest('id')
			->first();
	} catch (\Throwable $e) {
		$welcomePromo = null;
	}
	}

	if ($hasBudget()) {
	try {
		$heroBanners = Promocion::query()
			->where('tipo', 'hero_banner')
			->where('esta_activa', true)
			->orderBy('orden')
			->latest('id')
			->get();
	} catch (\Throwable $e) {
		$heroBanners = collect();
	}
	}

	if ($hasBudget()) {
	try {
		$secondaryBanners = Promocion::query()
			->where('tipo', 'secondary_banner')
			->where('esta_activa', true)
			->orderBy('orden')
			->latest('id')
			->get();
	} catch (\Throwable $e) {
		$secondaryBanners = collect();
	}
	}

	if ($hasBudget()) {
	try {
		$gafasBase = Producto::query()
			->whereIn('tipo', $gafasTipos)
			->where('esta_activo', true);

		// Más comprada: suma cantidades de items_carrito en pagos aprobados.
		$mostPurchased = DB::table('pagos')
			->join('carritos', 'pagos.carrito_id', '=', 'carritos.id')
			->join('items_carrito', 'items_carrito.carrito_id', '=', 'carritos.id')
			->join('productos', 'productos.id', '=', 'items_carrito.producto_id')
			->where('pagos.estado', 'aprobado')
			->whereNotNull('items_carrito.producto_id')
			->whereIn('productos.tipo', $gafasTipos)
			->where('productos.esta_activo', true)
			->groupBy('productos.id')
			->select('productos.id', DB::raw('SUM(items_carrito.cantidad) as qty'))
			->orderByDesc('qty')
			->orderByDesc('productos.id')
			->first();

		if ($mostPurchased && isset($mostPurchased->id)) {
			$editorialMostPurchased = (clone $gafasBase)->whereKey((int) $mostPurchased->id)->first();
		}

		$editorialNewest = (clone $gafasBase)
			->orderByDesc('created_at')
			->orderByDesc('id')
			->first();

		try {
			$editorialTopVisited = (clone $gafasBase)
				->orderByDesc('views_count')
				->orderByDesc('id')
				->first();

			$topProducts = (clone $gafasBase)
				->orderByDesc('views_count')
				->orderByDesc('id')
				->limit(4)
				->get();
		} catch (\Throwable $e) {
			$topProducts = collect();
		}
	} catch (\Throwable $e) {
		$topProducts = collect();
		$editorialMostPurchased = null;
		$editorialTopVisited = null;
		$editorialNewest = null;
	}
	}

	if ($hasBudget()) {
	try {
			$reviewsCount = (int) Resena::query()->count();
			$reviewsAvg = (float) (Resena::query()->avg('estrellas') ?? 0);
			$latestReviews = Resena::query()
				->with(['usuario:id,nombre'])
				->latest('id')
				->limit(12)
				->get();

			$reviewUserIds = $latestReviews->pluck('usuario_id')->filter()->unique()->values();
			if ($reviewUserIds->isNotEmpty()) {
				$verifiedBuyerUserIds = DB::table('pagos')
					->join('carritos', 'pagos.carrito_id', '=', 'carritos.id')
					->where('pagos.estado', 'aprobado')
					->whereIn('carritos.usuario_id', $reviewUserIds->all())
					->distinct()
					->pluck('carritos.usuario_id')
					->map(fn ($id) => (int) $id)
					->all();
			}
	} catch (\Throwable $e) {
		$reviewsAvg = 0;
		$reviewsCount = 0;
		$latestReviews = collect();
		$verifiedBuyerUserIds = [];
	}
	}

	$landingContent = [
		'landingIntro' => LandingIntroContent::defaults(),
		'landingBenefits' => LandingBenefitsContent::defaults(),
		'landingBenefitStrip' => LandingBenefitStripContent::defaults(),
		'landingCategoryPhotos' => LandingCategoryPhotosContent::defaults(),
		'landingCategories' => LandingCategoriesContent::defaults(),
		'landingPromoBanners' => LandingPromoBannersContent::defaults(),
		'landingServices' => LandingServicesContent::defaults(),
		'landingHighlights' => LandingHighlightsContent::defaults(),
		'landingHeroCarousel' => LandingHeroCarouselContent::defaults(),
		'landingSecondaryCarousel' => LandingSecondaryCarouselContent::defaults(),
		'landingHowItWorks' => LandingHowItWorksContent::defaults(),
		'landingEssentialBenefits' => LandingEssentialBenefitsContent::defaults(),
		'landingFaq' => LandingFaqContent::defaults(),
		'landingLocation' => LandingLocationContent::defaults(),
		'landingNewsletter' => LandingNewsletterContent::defaults(),
		'landingContact' => LandingContactContent::defaults(),
		'landingWhatsapp' => LandingWhatsappContent::defaults(),
		'landingFooter' => LandingFooterContent::defaults(),
	];

	$landingContent['landingIntro'] = $safeLoad(fn () => LandingIntroContent::load(), $landingContent['landingIntro']);
	$landingContent['landingBenefits'] = $safeLoad(fn () => LandingBenefitsContent::load(), $landingContent['landingBenefits']);
	$landingContent['landingBenefitStrip'] = $safeLoad(fn () => LandingBenefitStripContent::load(), $landingContent['landingBenefitStrip']);
	$landingContent['landingCategoryPhotos'] = $safeLoad(fn () => LandingCategoryPhotosContent::load(), $landingContent['landingCategoryPhotos']);
	$landingContent['landingCategories'] = $safeLoad(fn () => LandingCategoriesContent::load(), $landingContent['landingCategories']);
	$landingContent['landingPromoBanners'] = $safeLoad(fn () => LandingPromoBannersContent::load(), $landingContent['landingPromoBanners']);
	$landingContent['landingServices'] = $safeLoad(fn () => LandingServicesContent::load(), $landingContent['landingServices']);
	$landingContent['landingHighlights'] = $safeLoad(fn () => LandingHighlightsContent::load(), $landingContent['landingHighlights']);
	$landingContent['landingHeroCarousel'] = $safeLoad(fn () => LandingHeroCarouselContent::load(), $landingContent['landingHeroCarousel']);
	$landingContent['landingSecondaryCarousel'] = $safeLoad(fn () => LandingSecondaryCarouselContent::load(), $landingContent['landingSecondaryCarousel']);
	$landingContent['landingHowItWorks'] = $safeLoad(fn () => LandingHowItWorksContent::load(), $landingContent['landingHowItWorks']);
	$landingContent['landingEssentialBenefits'] = $safeLoad(fn () => LandingEssentialBenefitsContent::load(), $landingContent['landingEssentialBenefits']);
	$landingContent['landingFaq'] = $safeLoad(fn () => LandingFaqContent::load(), $landingContent['landingFaq']);
	$landingContent['landingLocation'] = $safeLoad(fn () => LandingLocationContent::load(), $landingContent['landingLocation']);
	$landingContent['landingNewsletter'] = $safeLoad(fn () => LandingNewsletterContent::load(), $landingContent['landingNewsletter']);
	$landingContent['landingContact'] = $safeLoad(fn () => LandingContactContent::load(), $landingContent['landingContact']);
	$landingContent['landingWhatsapp'] = $safeLoad(fn () => LandingWhatsappContent::load(), $landingContent['landingWhatsapp']);
	$landingContent['landingFooter'] = $safeLoad(fn () => LandingFooterContent::load(), $landingContent['landingFooter']);

	$landingView = request()->routeIs('landing', 'landing2') ? 'landing2' : 'landing';

	return view($landingView, [
		'welcomePromo' => $welcomePromo,
		'heroBanners' => $heroBanners,
		'secondaryBanners' => $secondaryBanners,
		'topProducts' => $topProducts,
		'editorialMostPurchased' => $editorialMostPurchased,
		'editorialTopVisited' => $editorialTopVisited,
		'editorialNewest' => $editorialNewest,
		'reviewsAvg' => $reviewsAvg,
		'reviewsCount' => $reviewsCount,
		'latestReviews' => $latestReviews,
		'verifiedBuyerUserIds' => $verifiedBuyerUserIds,
		'landingIntro' => $landingContent['landingIntro'] ?? LandingIntroContent::defaults(),
		'landingBenefits' => $landingContent['landingBenefits'] ?? LandingBenefitsContent::defaults(),
		'landingBenefitStrip' => $landingContent['landingBenefitStrip'] ?? LandingBenefitStripContent::defaults(),
		'landingCategoryPhotos' => $landingContent['landingCategoryPhotos'] ?? LandingCategoryPhotosContent::defaults(),
		'landingCategories' => $landingContent['landingCategories'] ?? LandingCategoriesContent::defaults(),
		'landingPromoBanners' => $landingContent['landingPromoBanners'] ?? LandingPromoBannersContent::defaults(),
		'landingServices' => $landingContent['landingServices'] ?? LandingServicesContent::defaults(),
		'landingHighlights' => $landingContent['landingHighlights'] ?? LandingHighlightsContent::defaults(),
		'landingHeroCarousel' => $landingContent['landingHeroCarousel'] ?? LandingHeroCarouselContent::defaults(),
		'landingSecondaryCarousel' => $landingContent['landingSecondaryCarousel'] ?? LandingSecondaryCarouselContent::defaults(),
		'landingHowItWorks' => $landingContent['landingHowItWorks'] ?? LandingHowItWorksContent::defaults(),
		'landingEssentialBenefits' => $landingContent['landingEssentialBenefits'] ?? LandingEssentialBenefitsContent::defaults(),
		'landingFaq' => $landingContent['landingFaq'] ?? LandingFaqContent::defaults(),
		'landingLocation' => $landingContent['landingLocation'] ?? LandingLocationContent::defaults(),
		'landingNewsletter' => $landingContent['landingNewsletter'] ?? LandingNewsletterContent::defaults(),
		'landingContact' => $landingContent['landingContact'] ?? LandingContactContent::defaults(),
		'landingWhatsapp' => $landingContent['landingWhatsapp'] ?? LandingWhatsappContent::defaults(),
		'landingFooter' => $landingContent['landingFooter'] ?? LandingFooterContent::defaults(),
	]);
};

Route::get('/', $landingHandler)->name('landing');
Route::get('/landing2', $landingHandler)->name('landing2');
Route::redirect('/tarjeta', 'https://estepastel.my.canva.site/', 301)->name('tarjeta.redirect');

Route::view('/arreglos-tecnicos-home', 'arreglos-tecnicos')->name('landing.arreglos');

Route::post('/newsletter', [NewsletterSubscriptionController::class, 'store'])
	->middleware(['web'])
	->name('newsletter.subscribe');

Route::get('/hero-banners/{hero_banner}/image', [HeroBannerController::class, 'image'])
	->middleware(['web'])
	->name('hero-banners.image');

Route::get('/secondary-banners/{secondary_banner}/image', [SecondaryBannerController::class, 'image'])
	->middleware(['web'])
	->name('secondary-banners.image');

Route::get('/contenido-archivos/{asset}', [ContentBlockAssetController::class, 'show'])
	->middleware(['web'])
	->name('content-block-assets.show');

Route::post('/resenas', [ResenaController::class, 'store'])
	->middleware(['web'])
	->name('resenas.store');

Route::delete('/resenas/{resena}', [ResenaController::class, 'destroy'])
	->middleware(['web', 'auth'])
	->name('resenas.destroy');

Route::get('/resenas', [ResenaController::class, 'index'])
	->middleware(['web'])
	->name('resenas.index');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/test-mail', TestMailController::class)
	->middleware(['web'])
	->name('test-mail');

Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.show');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/register/verify', [AuthController::class, 'showVerificationForm'])->name('register.verify.show');
Route::post('/register/verify', [AuthController::class, 'verifyRegistrationCode'])->name('register.verify');

// Recuperación de contraseña mediante código
Route::get('/password/forgot', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot.show');
Route::post('/password/forgot', [AuthController::class, 'sendForgotPasswordCode'])->name('password.forgot.send');
Route::get('/password/forgot/codigo', [AuthController::class, 'showForgotPasswordCodeForm'])->name('password.forgot.code.show');
Route::post('/password/forgot/codigo', [AuthController::class, 'verifyForgotPasswordCode'])->name('password.forgot.code.verify');
Route::get('/password/forgot/nueva-contrasena', [AuthController::class, 'showForgotPasswordResetForm'])->name('password.forgot.reset.show');
Route::post('/password/forgot/nueva-contrasena', [AuthController::class, 'completeForgotPasswordReset'])->name('password.forgot.reset');

Route::match(['GET', 'POST'], '/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('configuracion')
	->middleware(['web', 'admin'])
	->name('configuracion.')
	->group(function () {
		Route::get('/', [DashboardController::class, 'index'])->name('index');
		Route::post('company-notification-emails', [DashboardController::class, 'updateCompanyNotificationEmails'])->name('company-notification-emails.update');
		Route::get('landing-benefit-strip', [DashboardController::class, 'editLandingBenefitStrip'])->name('landing-benefit-strip.edit');
		Route::post('landing-benefit-strip', [DashboardController::class, 'updateLandingBenefitStrip'])->name('landing-benefit-strip.update');
		Route::get('landing-category-photos', [DashboardController::class, 'editLandingCategoryPhotos'])->name('landing-category-photos.edit');
		Route::post('landing-category-photos', [DashboardController::class, 'updateLandingCategoryPhotos'])->name('landing-category-photos.update');
		Route::get('landing-location', [DashboardController::class, 'editLandingLocation'])->name('landing-location.edit');
		Route::post('landing-location', [DashboardController::class, 'updateLandingLocation'])->name('landing-location.update');
		Route::get('landing-footer-faq', [DashboardController::class, 'editLandingFooterFaq'])->name('landing-footer-faq.edit');
		Route::post('landing-footer-faq', [DashboardController::class, 'updateLandingFooterFaq'])->name('landing-footer-faq.update');
		Route::post('hero-banners/settings', [HeroBannerController::class, 'updateCarouselSettings'])->name('hero-banners.settings.update');
		Route::resource('hero-banners', HeroBannerController::class)
			->parameters(['hero-banners' => 'hero_banner'])
			->except(['show']);
		Route::post('secondary-banners/settings', [SecondaryBannerController::class, 'updateCarouselSettings'])->name('secondary-banners.settings.update');
		Route::resource('secondary-banners', SecondaryBannerController::class)
			->parameters(['secondary-banners' => 'secondary_banner'])
			->except(['show']);
	});

Route::get('/dashboard', [DashboardController::class, 'dashboard'])
	->middleware(['admin'])
	->name('dashboard');

Route::get('/dashboard/formula-images', [DashboardController::class, 'editFormulaImages'])
	->middleware(['admin'])
	->name('dashboard.formula-images');

Route::post('/dashboard/formula-images', [DashboardController::class, 'updateFormulaImages'])
	->middleware(['web', 'admin'])
	->name('dashboard.formula-images.update');

Route::post('/dashboard/gafas-promo', [DashboardController::class, 'updateGafasPromo'])
	->middleware(['web', 'admin'])
	->name('dashboard.gafas-promo.update');

Route::middleware(['web', 'admin'])->group(function () {
	Route::get('/dashboard/precios-naratodo', [\App\Http\Controllers\DashboardNaraPricingController::class, 'edit'])
		->name('dashboard.precios-naratodo.edit');
	Route::put('/dashboard/precios-naratodo', [\App\Http\Controllers\DashboardNaraPricingController::class, 'update'])
		->name('dashboard.precios-naratodo.update');
});
Route::get('/dashboard/usuarios', [UserManagementController::class, 'index'])
	->middleware(['admin3'])
	->name('admin');

Route::get('/admin', function () {
	return redirect()->route('admin');
})->middleware(['admin3']);

Route::get('/gafas-mujeres', [GafasMujeresController::class, 'index'])->name('gafas-mujeres.index');
Route::get('/gafas-niñas', [GafasNinasController::class, 'index'])->name('gafas-ninas.index');
Route::get('/gafas-niños', [GafasNinosController::class, 'index'])->name('gafas-ninos.index');
Route::get('/gafas-polarizadas', [GafasPolarizadasController::class, 'index'])->name('gafas-polarizadas.index');
Route::get('/Polarizadas', [GafasPolarizadasController::class, 'index'])->name('polarizadas.index');
Route::get('/gafas-hombre', [GafasHombreController::class, 'index'])->name('gafas-hombre.index');
Route::get('/Hombre', [GafasHombreController::class, 'index'])->name('hombre.index');
Route::get('/gafas', [GafasController::class, 'index'])->name('gafas.index');
Route::get('/gafas-deportivas', [GafasController::class, 'index'])->name('gafas-deportivas.index');
Route::get('/gafas-descanso', function (\Illuminate\Http\Request $request) {
	$query = $request->query();
	return redirect()->route('gafas-deportivas.index', $query);
});
Route::get('/gafas/{producto:slug}', [GafaController::class, 'show'])->name('gafas.show');
Route::post('/gafas/{producto:slug}/analizar-receta-pdf', [\App\Http\Controllers\GafaPrescriptionController::class, 'analyze'])
	->name('gafas.prescription.analyze');

Route::post('/gafas/{producto:slug}/checkout-receta-pdf', [\App\Http\Controllers\GafaPrescriptionCheckoutController::class, 'storeAndRedirect'])
	->name('gafas.prescription.checkout');
Route::get('/gafas/{producto:slug}/checkout-receta-pdf', function (\Illuminate\Http\Request $request, \App\Models\Producto $producto) {
	$params = array_merge(['producto' => $producto->slug], $request->query());

	return redirect()->route('checkout.gafa', $params);
});

// Checkout invitado (sin obligar a registro)
Route::get('/checkout/gafas/{producto:slug}/invitado', [CheckoutController::class, 'gafaInvitado'])->name('checkout.gafa.invitado');
Route::post('/pagar/invitado/{producto:slug}', [PagosController::class, 'startGuest'])->name('pagos.startGuest');

Route::get('/pago/{pago}', [PagosController::class, 'show'])->name('pagos.show');

Route::middleware(['web'])->group(function () {
	Route::get('/seguimiento-pedido', SeguimientoPedidoController::class)->name('pedido.tracking');

	Route::get('/favoritos', [FavoritoController::class, 'index'])->name('favoritos.index');
	Route::post('/favoritos/{producto:slug}', [FavoritoController::class, 'toggle'])->name('favoritos.toggle');

	Route::get('/checkout/gafas/{producto:slug}', [CheckoutController::class, 'gafa'])->name('checkout.gafa');

	Route::post('/pagar/{producto:slug}', [PagosController::class, 'start'])->name('pagos.start');

});


Route::middleware(['auth'])->group(function () {
	Route::delete('/perfiles-cliente/{perfil}', [PerfilClienteController::class, 'destroy'])->name('perfiles-cliente.destroy');

});


Route::middleware(['admin'])->group(function () {
	Route::get('/dashboard/proximos-envios', [\App\Http\Controllers\ProximosEnviosController::class, 'index'])->name('dashboard.proximos-envios');
	Route::get('/dashboard/proximos-envios/pulse', [\App\Http\Controllers\ProximosEnviosController::class, 'pulse'])->name('dashboard.proximos-envios.pulse');
	Route::post('/dashboard/proximos-envios/{pago}/marcar-enviado', [\App\Http\Controllers\ProximosEnviosController::class, 'markAsSent'])->name('dashboard.proximos-envios.mark-sent');
	Route::post('/dashboard/proximos-envios/{pago}/upload-shipping', [\App\Http\Controllers\ProximosEnviosController::class, 'uploadShipping'])->name('dashboard.proximos-envios.upload-shipping');
	Route::get('/dashboard/pagos/{pago}/formula', [\App\Http\Controllers\DashboardPagoPrescriptionController::class, 'show'])->name('dashboard.pagos.formula');
});

// Descarga pública del archivo de envío asociado a un pago (si existe)
Route::get('/pago/{pago}/shipping-file', [\App\Http\Controllers\ProximosEnviosController::class, 'downloadShippingFile'])->name('pagos.shipping-file');

// Dummy (simulación) - también debe permitir checkout invitado.
Route::get('/pago/{pago}/dummy', [PagosController::class, 'dummy'])->name('pagos.dummy');
Route::post('/pago/{pago}/dummy/confirm', [PagosController::class, 'dummyConfirm'])->name('pagos.dummy.confirm');
Route::get('/pago/{pago}/aprobado', [PagosController::class, 'approved'])->name('pagos.approved');

// Bold: atajo para probar/usar retorno con URL por pago (estilo dummy).
// Redirige al retorno oficial con la referencia del pago.
Route::get('/pago/{pago}/bold/retorno', function (\App\Models\Pago $pago) {
	abort_unless($pago->pasarela === 'bold', 404);

	return redirect()->route('pagos.retorno', [
		'driver' => 'bold',
		'ref' => $pago->referencia,
	]);
})->name('pagos.bold.retorno');

Route::get('/pagos/retorno/{driver}', [PagosRetornoController::class, 'handle'])->name('pagos.retorno');
Route::post('/webhooks/pagos/{driver}', [PagosWebhookController::class, 'handle'])
	->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
	->middleware(['throttle:60,1'])
	->name('pagos.webhook');

Route::prefix('dashboard')
	->middleware(['web', 'admin3'])
	->name('admin.')
	->group(function () {
		Route::get('/empleados', [UserManagementController::class, 'empleados'])->name('empleados');
		Route::get('/correos-empresa', [UserManagementController::class, 'companyEmails'])->name('company-emails');
		Route::post('/correos-empresa/agregar', [UserManagementController::class, 'addCompanyEmail'])->name('company-emails.add');
		Route::patch('/correos-empresa/editar', [UserManagementController::class, 'updateCompanyEmail'])->name('company-emails.edit');
		Route::delete('/correos-empresa/eliminar', [UserManagementController::class, 'deleteCompanyEmail'])->name('company-emails.delete');
		Route::post('/correos-empresa', [UserManagementController::class, 'updateCompanyEmails'])->name('company-emails.update');
		Route::patch('/usuarios/{usuario}/rol', [UserManagementController::class, 'updateRole'])->name('usuarios.update-role');
		Route::get('/informacion', [InformacionController::class, 'index'])->name('informacion');
		Route::post('/informacion/love-cta', [InformacionController::class, 'updateLoveCtaVisibility'])->name('informacion.love-cta');
	});

	Route::prefix('dashboard')
		->middleware(['web', 'admin'])
		->name('admin.')
		->group(function () {
		Route::resource('gafas-mujeres', AdminGafasMujeresController::class)
			->parameters(['gafas-mujeres' => 'producto'])
			->except(['show']);

		Route::resource('gafas-descanso', AdminGafasMujeresController::class)
			->parameters(['gafas-descanso' => 'producto'])
			->except(['show']);

		Route::post('gafas-mujeres/promo-image', [AdminGafasMujeresController::class, 'updatePromoImage'])
			->name('gafas-mujeres.promo-image.update');

		Route::get('gafas-mujeres/import/form', [AdminGafasMujeresController::class, 'showImportForm'])
			->name('gafas-mujeres.import.show');

		Route::post('gafas-mujeres/import', [AdminGafasMujeresController::class, 'importFromExcel'])
			->name('gafas-mujeres.import');

		Route::get('gafas-mujeres/import/template', [AdminGafasMujeresController::class, 'downloadTemplate'])
			->name('gafas-mujeres.import.template');

		Route::post('gafas-descanso/promo-image', [AdminGafasMujeresController::class, 'updatePromoImage'])
			->name('gafas-descanso.promo-image.update');

		Route::get('gafas-descanso/import/form', [AdminGafasMujeresController::class, 'showImportForm'])
			->name('gafas-descanso.import.show');

		Route::post('gafas-descanso/import', [AdminGafasMujeresController::class, 'importFromExcel'])
			->name('gafas-descanso.import');

		Route::get('gafas-descanso/import/template', [AdminGafasMujeresController::class, 'downloadTemplate'])
			->name('gafas-descanso.import.template');

		Route::resource('gafas-ninas', AdminGafasNinasController::class)
			->parameters(['gafas-ninas' => 'producto'])
			->except(['show']);

		Route::post('gafas-ninas/promo-image', [AdminGafasNinasController::class, 'updatePromoImage'])
			->name('gafas-ninas.promo-image.update');

		Route::get('gafas-ninas/import/form', [AdminGafasNinasController::class, 'showImportForm'])
			->name('gafas-ninas.import.show');

		Route::post('gafas-ninas/import', [AdminGafasNinasController::class, 'importFromExcel'])
			->name('gafas-ninas.import');

		Route::get('gafas-ninas/import/template', [AdminGafasNinasController::class, 'downloadTemplate'])
			->name('gafas-ninas.import.template');

		Route::resource('gafas-ninos', AdminGafasNinosController::class)
			->parameters(['gafas-ninos' => 'producto'])
			->except(['show']);

		Route::post('gafas-ninos/promo-image', [AdminGafasNinosController::class, 'updatePromoImage'])
			->name('gafas-ninos.promo-image.update');

		Route::get('gafas-ninos/import/form', [AdminGafasNinosController::class, 'showImportForm'])
			->name('gafas-ninos.import.show');

		Route::post('gafas-ninos/import', [AdminGafasNinosController::class, 'importFromExcel'])
			->name('gafas-ninos.import');

		Route::get('gafas-ninos/import/template', [AdminGafasNinosController::class, 'downloadTemplate'])
			->name('gafas-ninos.import.template');

		Route::resource('gafas-polarizadas', AdminGafasPolarizadasController::class)
			->parameters(['gafas-polarizadas' => 'producto'])
			->except(['show']);

		Route::post('gafas-polarizadas/promo-image', [AdminGafasPolarizadasController::class, 'updatePromoImage'])
			->name('gafas-polarizadas.promo-image.update');

		Route::get('gafas-polarizadas/import/form', [AdminGafasPolarizadasController::class, 'showImportForm'])
			->name('gafas-polarizadas.import.show');

		Route::post('gafas-polarizadas/import', [AdminGafasPolarizadasController::class, 'importFromExcel'])
			->name('gafas-polarizadas.import');

		Route::get('gafas-polarizadas/import/template', [AdminGafasPolarizadasController::class, 'downloadTemplate'])
			->name('gafas-polarizadas.import.template');

		Route::resource('gafas-hombre', AdminGafasHombreController::class)
			->parameters(['gafas-hombre' => 'producto'])
			->except(['show']);

		Route::post('gafas-hombre/promo-image', [AdminGafasHombreController::class, 'updatePromoImage'])
			->name('gafas-hombre.promo-image.update');

		Route::get('gafas-hombre/import/form', [AdminGafasHombreController::class, 'showImportForm'])
			->name('gafas-hombre.import.show');

		Route::post('gafas-hombre/import', [AdminGafasHombreController::class, 'importFromExcel'])
			->name('gafas-hombre.import');

		Route::get('gafas-hombre/import/template', [AdminGafasHombreController::class, 'downloadTemplate'])
			->name('gafas-hombre.import.template');
	});

Route::get('/admin/{path}', function (string $path) {
	return redirect('/dashboard/' . $path, 301);
})->where('path', '.*');


