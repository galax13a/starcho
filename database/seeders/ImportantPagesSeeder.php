<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\SiteLanguage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ImportantPagesSeeder extends Seeder
{
    public function run(): void
    {
        $activeCodes   = SiteLanguage::activeCodes();
        $primaryLocale = $activeCodes[0] ?? 'es';
        $author        = User::first();

        if (! $author) {
            $this->command->warn('No users found. Skipping ImportantPagesSeeder.');
            return;
        }

        $pages = [
            [
                'slug'         => ['es' => 'inicio',    'en' => 'home'],
                'menu_order'   => 1,
                'nav_position' => 'header',
                'title'   => ['es' => 'Inicio',    'en' => 'Home'],
                'excerpt' => [
                    'es' => 'Bienvenido a nuestra plataforma. Descubre todo lo que tenemos para ofrecerte.',
                    'en' => 'Welcome to our platform. Discover everything we have to offer you.',
                ],
                'seo_title' => [
                    'es' => 'Inicio — Tu plataforma digital',
                    'en' => 'Home — Your digital platform',
                ],
                'seo_description' => [
                    'es' => 'Bienvenido a nuestra plataforma. Soluciones innovadoras para impulsar tu negocio.',
                    'en' => 'Welcome to our platform. Innovative solutions to drive your business forward.',
                ],
                'content' => [
                    'es' => $this->homeEs(),
                    'en' => $this->homeEn(),
                ],
            ],
            [
                'slug'         => ['es' => 'nosotros', 'en' => 'about-us'],
                'menu_order'   => 2,
                'nav_position' => 'header',
                'title'   => ['es' => 'Nosotros',  'en' => 'About Us'],
                'excerpt' => [
                    'es' => 'Conoce quiénes somos, nuestra misión, visión y el equipo detrás del proyecto.',
                    'en' => 'Learn who we are, our mission, vision and the team behind the project.',
                ],
                'seo_title' => [
                    'es' => 'Nosotros — Quiénes somos',
                    'en' => 'About Us — Who we are',
                ],
                'seo_description' => [
                    'es' => 'Conoce la historia, misión y el equipo que hace posible nuestra plataforma.',
                    'en' => 'Learn the story, mission and the team that makes our platform possible.',
                ],
                'content' => [
                    'es' => $this->aboutEs(),
                    'en' => $this->aboutEn(),
                ],
            ],
            [
                'slug'         => ['es' => 'servicios', 'en' => 'services'],
                'menu_order'   => 3,
                'nav_position' => 'header',
                'title'   => ['es' => 'Servicios', 'en' => 'Services'],
                'excerpt' => [
                    'es' => 'Ofrecemos soluciones completas adaptadas a tus necesidades.',
                    'en' => 'We offer complete solutions tailored to your needs.',
                ],
                'seo_title' => [
                    'es' => 'Servicios — Qué ofrecemos',
                    'en' => 'Services — What we offer',
                ],
                'seo_description' => [
                    'es' => 'Desarrollo web, marketing digital, consultoría tecnológica y soporte técnico.',
                    'en' => 'Web development, digital marketing, technology consulting and technical support.',
                ],
                'content' => [
                    'es' => $this->servicesEs(),
                    'en' => $this->servicesEn(),
                ],
            ],
            [
                'slug'         => ['es' => 'blog', 'en' => 'blog'],
                'menu_order'   => 4,
                'nav_position' => 'header',
                'title'   => ['es' => 'Blog',      'en' => 'Blog'],
                'excerpt' => [
                    'es' => 'Artículos, tutoriales y novedades del sector.',
                    'en' => 'Articles, tutorials and industry news.',
                ],
                'seo_title' => [
                    'es' => 'Blog — Artículos y tutoriales',
                    'en' => 'Blog — Articles and tutorials',
                ],
                'seo_description' => [
                    'es' => 'Lee nuestros últimos artículos, tutoriales y casos de éxito del sector.',
                    'en' => 'Read our latest articles, tutorials and industry success stories.',
                ],
                'content' => [
                    'es' => $this->blogEs(),
                    'en' => $this->blogEn(),
                ],
            ],
            [
                'slug'         => ['es' => 'contacto', 'en' => 'contact'],
                'menu_order'   => 5,
                'nav_position' => 'header',
                'title'   => ['es' => 'Contacto',  'en' => 'Contact'],
                'excerpt' => [
                    'es' => 'Estamos aquí para ayudarte. Respondemos en menos de 24 horas.',
                    'en' => 'We are here to help you. We respond in less than 24 hours.',
                ],
                'seo_title' => [
                    'es' => 'Contacto — Escríbenos',
                    'en' => 'Contact — Get in touch',
                ],
                'seo_description' => [
                    'es' => 'Contáctanos por email, teléfono o formulario. Respondemos en menos de 24 horas.',
                    'en' => 'Contact us by email, phone or form. We respond in less than 24 hours.',
                ],
                'content' => [
                    'es' => $this->contactEs(),
                    'en' => $this->contactEn(),
                ],
            ],
            [
                'slug'         => ['es' => 'politica-de-privacidad', 'en' => 'privacy-policy'],
                'menu_order'   => 6,
                'nav_position' => 'footer',
                'title'   => ['es' => 'Política de Privacidad', 'en' => 'Privacy Policy'],
                'excerpt' => [
                    'es' => 'Información sobre cómo recopilamos, usamos y protegemos tus datos personales.',
                    'en' => 'Information on how we collect, use and protect your personal data.',
                ],
                'seo_title' => [
                    'es' => 'Política de Privacidad',
                    'en' => 'Privacy Policy',
                ],
                'seo_description' => [
                    'es' => 'Conoce cómo tratamos y protegemos tu información personal según la normativa vigente.',
                    'en' => 'Learn how we handle and protect your personal information under applicable regulations.',
                ],
                'content' => [
                    'es' => $this->privacyEs(),
                    'en' => $this->privacyEn(),
                ],
            ],
        ];

        foreach ($pages as $data) {
            // Build the content JSON per locale (only active locales)
            $contentPerLocale = [];
            foreach ($activeCodes as $code) {
                $blocks = $data['content'][$code] ?? $data['content'][$primaryLocale];
                $contentPerLocale[$code] = json_encode($blocks);
            }

            // Filter translatable fields to only active locales
            $filter = fn (array $map) => array_filter(
                $map,
                fn ($v) => $v !== null && $v !== ''
            );

            // Slug per locale (fill missing locales with primary)
            $slugMap = [];
            foreach ($activeCodes as $code) {
                $slugMap[$code] = $data['slug'][$code] ?? $data['slug'][$primaryLocale];
            }

            // Find by primary locale slug via JSON_SEARCH, or fall back to creating
            $primarySlug = $slugMap[$primaryLocale];
            $existing = Post::where('type', 'page')
                ->whereRaw("JSON_SEARCH(slug, 'one', ?) IS NOT NULL", [$primarySlug])
                ->first();

            $attrs = [
                'type'            => 'page',
                'title'           => $filter($data['title']),
                'slug'            => $slugMap,
                'excerpt'         => $filter($data['excerpt']),
                'seo_title'       => $filter($data['seo_title']),
                'seo_description' => $filter($data['seo_description']),
                'content'         => $contentPerLocale,
                'status'          => 'published',
                'author_id'       => $author->id,
                'user_id'         => $author->id,
                'menu_order'      => $data['menu_order'],
                'nav_position'    => $data['nav_position'] ?? 'none',
                'allow_comments'  => false,
            ];

            if ($existing) {
                $existing->update($attrs);
            } else {
                Post::create($attrs);
            }

            $this->command->line("  <fg=green>✓</> <fg=cyan>{$data['title'][$primaryLocale]}</> / <fg=yellow>{$data['title']['en']}</>");
        }

        $this->command->info('6 important pages seeded/updated with ES + EN content (status: published).');
    }

    // ── HOME ─────────────────────────────────────────────────────────────────

    private function homeEs(): array
    {
        return $this->doc([
            $this->h2('Bienvenido a nuestra plataforma'),
            $this->p('Somos una empresa comprometida con ofrecer soluciones innovadoras que impulsen el crecimiento de tu negocio. Trabajamos con pasión, tecnología de vanguardia y un equipo de expertos dedicados a hacer realidad tus proyectos digitales.'),
            $this->h3('¿Por qué elegirnos?'),
            $this->ul(['Más de 10 años de experiencia en el sector', 'Equipo de profesionales certificados y apasionados', 'Soporte personalizado 24 / 7', 'Soluciones a medida para cada cliente', 'Resultados medibles desde el primer mes']),
            $this->h3('Nuestros números'),
            $this->p('+500 clientes satisfechos · +1,200 proyectos entregados · 98 % de satisfacción en encuestas post-servicio.'),
            $this->h3('¿Listo para empezar?'),
            $this->p('Cuéntanos tu idea. Estamos aquí para convertirla en una solución digital que marque la diferencia.'),
        ]);
    }

    private function homeEn(): array
    {
        return $this->doc([
            $this->h2('Welcome to our platform'),
            $this->p('We are a company committed to delivering innovative solutions that drive your business growth. We work with passion, cutting-edge technology and a team of experts dedicated to making your digital projects a reality.'),
            $this->h3('Why choose us?'),
            $this->ul(['Over 10 years of industry experience', 'Team of certified and passionate professionals', 'Personalised 24/7 support', 'Custom solutions for every client', 'Measurable results from the very first month']),
            $this->h3('Our numbers'),
            $this->p('+500 satisfied clients · +1,200 projects delivered · 98% satisfaction in post-service surveys.'),
            $this->h3('Ready to get started?'),
            $this->p('Tell us your idea. We are here to turn it into a digital solution that makes a difference.'),
        ]);
    }

    // ── ABOUT ────────────────────────────────────────────────────────────────

    private function aboutEs(): array
    {
        return $this->doc([
            $this->h2('Nuestra historia'),
            $this->p('Fundada con la misión de transformar la manera en que las empresas gestionan su presencia digital, hemos crecido hasta convertirnos en un referente del sector. Comenzamos como un pequeño estudio de dos personas y hoy somos un equipo multidisciplinar de más de 30 profesionales.'),
            $this->h3('Misión'),
            $this->p('Empoderar a personas y empresas con tecnología accesible, intuitiva y de alto rendimiento que simplifique sus procesos y amplíe su alcance.'),
            $this->h3('Visión'),
            $this->p('Ser la plataforma de referencia en gestión digital para medianas y grandes empresas en América Latina y España antes de 2030.'),
            $this->h3('Valores'),
            $this->ul(['Transparencia — comunicación honesta y sin letra pequeña', 'Innovación — buscamos siempre la mejor solución, no la más fácil', 'Compromiso — nos implicamos como si el proyecto fuera nuestro', 'Calidad — nada sale sin pasar por nuestros estándares', 'Personas — el talento humano es nuestro mayor activo']),
            $this->h3('Nuestro equipo'),
            $this->p('Diseñadores UX / UI, desarrolladores full-stack, especialistas en marketing digital y consultores estratégicos trabajan juntos para ofrecer resultados excepcionales en cada proyecto.'),
        ]);
    }

    private function aboutEn(): array
    {
        return $this->doc([
            $this->h2('Our story'),
            $this->p('Founded with the mission of transforming the way companies manage their digital presence, we have grown to become a benchmark in the industry. We started as a small two-person studio and today we are a multidisciplinary team of more than 30 professionals.'),
            $this->h3('Mission'),
            $this->p('To empower people and businesses with accessible, intuitive and high-performance technology that simplifies their processes and extends their reach.'),
            $this->h3('Vision'),
            $this->p('To be the go-to digital management platform for mid-size and large companies in Latin America and Spain before 2030.'),
            $this->h3('Values'),
            $this->ul(['Transparency — honest communication with no hidden clauses', 'Innovation — we always look for the best solution, not the easiest one', 'Commitment — we get involved as if the project were our own', 'Quality — nothing ships without meeting our standards', 'People — human talent is our greatest asset']),
            $this->h3('Our team'),
            $this->p('UX / UI designers, full-stack developers, digital marketing specialists and strategic consultants work together to deliver exceptional results on every project.'),
        ]);
    }

    // ── SERVICES ─────────────────────────────────────────────────────────────

    private function servicesEs(): array
    {
        return $this->doc([
            $this->h2('Lo que hacemos'),
            $this->p('Ofrecemos un portafolio completo de servicios digitales diseñados para impulsar tu negocio al siguiente nivel, desde la conceptualización hasta el lanzamiento y el mantenimiento continuo.'),
            $this->h3('Desarrollo web y aplicaciones'),
            $this->p('Diseñamos y desarrollamos sitios web, tiendas en línea y aplicaciones web a medida utilizando las tecnologías más modernas: Laravel, React, Vue.js, Tailwind CSS y más.'),
            $this->ul(['Sitios corporativos y landing pages de alta conversión', 'Tiendas e-commerce con pasarelas de pago integradas', 'Paneles de administración y dashboards personalizados', 'APIs REST y microservicios escalables']),
            $this->h3('Marketing digital y SEO'),
            $this->p('Aumentamos tu visibilidad en buscadores y redes sociales con estrategias basadas en datos y orientadas a resultados reales.'),
            $this->ul(['Auditoría y optimización SEO on-page y off-page', 'Campañas Google Ads y Meta Ads', 'Gestión de redes sociales y content marketing', 'Email marketing y automatización de funnels']),
            $this->h3('Consultoría tecnológica'),
            $this->p('Acompañamos tu transformación digital con asesoría experta en infraestructura cloud, arquitectura de sistemas, seguridad y elección de herramientas.'),
            $this->h3('Soporte y mantenimiento'),
            $this->p('Planes de soporte mensual que incluyen actualizaciones de seguridad, monitoreo 24 / 7, backups automáticos y atención prioritaria ante incidencias.'),
        ]);
    }

    private function servicesEn(): array
    {
        return $this->doc([
            $this->h2('What we do'),
            $this->p('We offer a complete portfolio of digital services designed to take your business to the next level, from conceptualisation through to launch and ongoing maintenance.'),
            $this->h3('Web and application development'),
            $this->p('We design and develop custom websites, online stores and web applications using the most modern technologies: Laravel, React, Vue.js, Tailwind CSS and more.'),
            $this->ul(['Corporate sites and high-conversion landing pages', 'E-commerce stores with integrated payment gateways', 'Custom admin panels and dashboards', 'Scalable REST APIs and microservices']),
            $this->h3('Digital marketing and SEO'),
            $this->p('We increase your visibility on search engines and social media with data-driven strategies focused on real results.'),
            $this->ul(['On-page and off-page SEO audit and optimisation', 'Google Ads and Meta Ads campaigns', 'Social media management and content marketing', 'Email marketing and funnel automation']),
            $this->h3('Technology consulting'),
            $this->p('We guide your digital transformation with expert advice on cloud infrastructure, systems architecture, security and tool selection.'),
            $this->h3('Support and maintenance'),
            $this->p('Monthly support plans including security updates, 24/7 monitoring, automatic backups and priority incident response.'),
        ]);
    }

    // ── BLOG ─────────────────────────────────────────────────────────────────

    private function blogEs(): array
    {
        return $this->doc([
            $this->h2('Nuestro blog'),
            $this->p('Bienvenido a nuestro espacio de conocimiento. Aquí compartimos artículos, tutoriales, casos de estudio y las últimas tendencias del sector tecnológico para ayudarte a tomar mejores decisiones.'),
            $this->h3('Categorías destacadas'),
            $this->ul(['Desarrollo web y tecnología', 'Marketing digital y SEO', 'Casos de éxito y testimonios de clientes', 'Tutoriales paso a paso', 'Noticias y tendencias del sector', 'Herramientas y recursos gratuitos']),
            $this->h3('Artículos más leídos'),
            $this->p('Explora nuestra selección de los artículos más valorados por nuestra comunidad: guías completas, comparativas de herramientas y entrevistas con expertos del sector.'),
            $this->h3('Newsletter'),
            $this->p('Suscríbete a nuestro newsletter semanal y recibe los mejores artículos, recursos gratuitos y noticias del sector directamente en tu bandeja de entrada. Sin spam, solo contenido de valor.'),
        ]);
    }

    private function blogEn(): array
    {
        return $this->doc([
            $this->h2('Our blog'),
            $this->p('Welcome to our knowledge hub. We share articles, tutorials, case studies and the latest technology industry trends to help you make better decisions.'),
            $this->h3('Featured categories'),
            $this->ul(['Web development and technology', 'Digital marketing and SEO', 'Client success stories and testimonials', 'Step-by-step tutorials', 'Industry news and trends', 'Free tools and resources']),
            $this->h3('Most-read articles'),
            $this->p('Explore our selection of the most valued articles by our community: complete guides, tool comparisons and interviews with industry experts.'),
            $this->h3('Newsletter'),
            $this->p('Subscribe to our weekly newsletter and receive the best articles, free resources and industry news straight to your inbox. No spam, only valuable content.'),
        ]);
    }

    // ── CONTACT ──────────────────────────────────────────────────────────────

    private function contactEs(): array
    {
        return $this->doc([
            $this->h2('Hablemos'),
            $this->p('Estamos aquí para escucharte. Cuéntanos sobre tu proyecto, tu reto o tu idea, y te responderemos en menos de 24 horas hábiles con una propuesta inicial sin compromiso.'),
            $this->h3('Información de contacto'),
            $this->ul(['📧  hola@tudominio.com', '📞  +1 (555) 000-0000', '📍  Ciudad, País', '🕐  Lunes a viernes: 9:00 – 18:00 (GMT-5)']),
            $this->h3('¿Cuándo te respondemos?'),
            $this->p('Nuestro equipo revisa todos los mensajes de lunes a viernes. Los mensajes recibidos fuera del horario laboral o durante fines de semana son atendidos el siguiente día hábil.'),
            $this->h3('Otros canales'),
            $this->ul(['LinkedIn: linkedin.com/company/tuempresa', 'Twitter / X: @tuempresa', 'WhatsApp Business: +1 (555) 000-0001']),
            $this->h3('Preguntas frecuentes'),
            $this->p('¿Tienes preguntas comunes sobre precios, plazos o tecnologías? Revisa nuestra sección de preguntas frecuentes antes de escribirnos; puede que ya tengamos la respuesta que buscas.'),
        ]);
    }

    private function contactEn(): array
    {
        return $this->doc([
            $this->h2("Let's talk"),
            $this->p('We are here to listen. Tell us about your project, challenge or idea and we will get back to you within 24 business hours with an initial no-commitment proposal.'),
            $this->h3('Contact information'),
            $this->ul(['📧  hello@yourdomain.com', '📞  +1 (555) 000-0000', '📍  City, Country', '🕐  Monday to Friday: 9:00 AM – 6:00 PM (GMT-5)']),
            $this->h3('When will you hear back?'),
            $this->p('Our team reviews all messages Monday through Friday. Messages received outside business hours or at weekends are handled the next business day.'),
            $this->h3('Other channels'),
            $this->ul(['LinkedIn: linkedin.com/company/yourcompany', 'Twitter / X: @yourcompany', 'WhatsApp Business: +1 (555) 000-0001']),
            $this->h3('Frequently asked questions'),
            $this->p('Have common questions about pricing, timelines or technologies? Check our FAQ section before writing to us — we may already have the answer you are looking for.'),
        ]);
    }

    // ── PRIVACY ──────────────────────────────────────────────────────────────

    private function privacyEs(): array
    {
        $date = now()->format('d/m/Y');
        return $this->doc([
            $this->h2('Política de Privacidad'),
            $this->p("Última actualización: {$date}"),
            $this->h3('1. Responsable del tratamiento'),
            $this->p('Tu Empresa S.L. — NIF: B-00000000 — hola@tudominio.com — Calle Ejemplo 1, Ciudad, País.'),
            $this->h3('2. Información que recopilamos'),
            $this->p('Recopilamos información que nos proporcionas directamente: nombre, correo electrónico, teléfono y cualquier dato que compartas al registrarte, rellenar formularios de contacto o usar nuestros servicios.'),
            $this->ul(['Datos de identificación: nombre y apellidos, correo electrónico', 'Datos de contacto: teléfono, dirección', 'Datos de uso: páginas visitadas, tiempo de sesión, acciones realizadas', 'Datos técnicos: dirección IP, tipo de navegador, sistema operativo']),
            $this->h3('3. Finalidad del tratamiento'),
            $this->ul(['Gestionar tu cuenta y prestarte los servicios contratados', 'Enviarte comunicaciones relacionadas con el servicio', 'Responder a tus consultas y solicitudes de soporte', 'Mejorar la plataforma mediante análisis de uso anónimo', 'Cumplir con obligaciones legales y fiscales']),
            $this->h3('4. Base legal'),
            $this->p('El tratamiento se basa en la ejecución del contrato de servicio (art. 6.1.b RGPD), en el interés legítimo para el análisis de uso (art. 6.1.f RGPD) y en el consentimiento explícito cuando así se solicite (art. 6.1.a RGPD).'),
            $this->h3('5. Conservación de datos'),
            $this->p('Conservamos tus datos mientras mantengas una cuenta activa o hasta que solicites su supresión. Los datos de facturación se conservan durante el período mínimo exigido por la legislación fiscal aplicable.'),
            $this->h3('6. Compartición con terceros'),
            $this->p('No vendemos ni cedemos tus datos personales a terceros con fines comerciales. Podemos compartir datos con proveedores de servicios (hosting, pago, análisis) bajo contratos de encargado de tratamiento que garantizan el mismo nivel de protección.'),
            $this->h3('7. Tus derechos'),
            $this->ul(['Acceso — obtener copia de tus datos personales', 'Rectificación — corregir datos inexactos o incompletos', 'Supresión — solicitar el borrado de tus datos', 'Limitación — restringir el tratamiento en ciertos casos', 'Portabilidad — recibir tus datos en formato estructurado', 'Oposición — oponerte al tratamiento basado en interés legítimo']),
            $this->p('Para ejercer tus derechos escríbenos a privacidad@tudominio.com adjuntando copia de tu documento de identidad.'),
            $this->h3('8. Seguridad'),
            $this->p('Aplicamos medidas técnicas y organizativas apropiadas: cifrado TLS en tránsito, cifrado en reposo para datos sensibles, controles de acceso basados en roles y auditorías periódicas de seguridad.'),
            $this->h3('9. Cambios en esta política'),
            $this->p('Podemos actualizar esta política periódicamente. Te notificaremos los cambios relevantes por correo electrónico o mediante un aviso destacado en la plataforma.'),
            $this->h3('10. Contacto'),
            $this->p('Delegado de Protección de Datos: privacidad@tudominio.com'),
        ]);
    }

    private function privacyEn(): array
    {
        $date = now()->format('m/d/Y');
        return $this->doc([
            $this->h2('Privacy Policy'),
            $this->p("Last updated: {$date}"),
            $this->h3('1. Data controller'),
            $this->p('Your Company Ltd. — hello@yourdomain.com — 1 Example Street, City, Country.'),
            $this->h3('2. Information we collect'),
            $this->p('We collect information you provide directly: name, email address, phone number and any data you share when registering, completing contact forms or using our services.'),
            $this->ul(['Identification data: first and last name, email address', 'Contact data: phone number, address', 'Usage data: pages visited, session duration, actions taken', 'Technical data: IP address, browser type, operating system']),
            $this->h3('3. Purpose of processing'),
            $this->ul(['Manage your account and provide the contracted services', 'Send you service-related communications', 'Respond to your enquiries and support requests', 'Improve the platform through anonymised usage analytics', 'Comply with legal and fiscal obligations']),
            $this->h3('4. Legal basis'),
            $this->p('Processing is based on the performance of the service contract (Art. 6.1.b GDPR), on legitimate interest for usage analytics (Art. 6.1.f GDPR) and on explicit consent where requested (Art. 6.1.a GDPR).'),
            $this->h3('5. Data retention'),
            $this->p('We retain your data while you maintain an active account or until you request deletion. Billing data is retained for the minimum period required by applicable tax law.'),
            $this->h3('6. Sharing with third parties'),
            $this->p('We do not sell or transfer your personal data to third parties for commercial purposes. We may share data with service providers (hosting, payment, analytics) under data processing agreements that guarantee the same level of protection.'),
            $this->h3('7. Your rights'),
            $this->ul(['Access — obtain a copy of your personal data', 'Rectification — correct inaccurate or incomplete data', 'Erasure — request deletion of your data', 'Restriction — limit processing in certain cases', 'Portability — receive your data in a structured format', 'Objection — object to processing based on legitimate interest']),
            $this->p('To exercise your rights, write to us at privacy@yourdomain.com attaching a copy of your identity document.'),
            $this->h3('8. Security'),
            $this->p('We apply appropriate technical and organisational measures: TLS encryption in transit, encryption at rest for sensitive data, role-based access controls and periodic security audits.'),
            $this->h3('9. Changes to this policy'),
            $this->p('We may update this policy periodically. We will notify you of material changes by email or via a prominent notice on the platform.'),
            $this->h3('10. Contact'),
            $this->p('Data Protection Officer: privacy@yourdomain.com'),
        ]);
    }

    // ── Builder helpers ───────────────────────────────────────────────────────

    private function doc(array $blocks): array
    {
        return ['time' => time() * 1000, 'version' => '2.28.0', 'blocks' => $blocks];
    }

    private function h2(string $text): array
    {
        return ['type' => 'header', 'data' => ['text' => $text, 'level' => 2]];
    }

    private function h3(string $text): array
    {
        return ['type' => 'header', 'data' => ['text' => $text, 'level' => 3]];
    }

    private function p(string $text): array
    {
        return ['type' => 'paragraph', 'data' => ['text' => $text]];
    }

    private function ul(array $items): array
    {
        return ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => $items]];
    }
}
