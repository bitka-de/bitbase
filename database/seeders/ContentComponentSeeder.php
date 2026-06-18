<?php

namespace Database\Seeders;

use App\Models\ContentComponent;
use Illuminate\Database\Seeder;

class ContentComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            [
                'name' => 'button',
                'title' => 'Button',
                'description' => 'Ein einfacher Button/Link mit Styling.',
                'tags' => ['cta', 'ui', 'navigation'],
                'content' => '<a href="#" class="btn btn-primary">Jetzt starten</a>',
                'css' => '',
                'js' => '',
            ],
            [
                'name' => 'card',
                'title' => 'Karte / Card',
                'description' => 'Eine Karte mit Titel, Beschreibung und optionalem Bild.',
                'tags' => ['layout', 'content', 'ui'],
                'content' => '<div class="card" style="border: 1px solid #e0e0e0; border-radius: 0.75rem; padding: 1.25rem; max-width: 320px;">
  <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; font-weight: 600;">Kartentitel</h3>
  <p style="margin: 0; color: #666; font-size: 0.95rem;">Kurze Beschreibung oder Text für diese Karte.</p>
</div>',
                'css' => '',
                'js' => '',
            ],
            [
                'name' => 'hero',
                'title' => 'Hero-Bereich',
                'description' => 'Ein großer Hero-Bereich mit Titel, Untertitel und CTA.',
                'tags' => ['hero', 'marketing', 'landing'],
                'content' => '<section style="padding: 3rem 1.5rem; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 1rem;">
  <h1 style="margin: 0 0 1rem; font-size: 2.2rem; font-weight: 700;">Hero Titel</h1>
  <p style="margin: 0 0 1.5rem; font-size: 1.1rem; max-width: 600px; margin-left: auto; margin-right: auto;">Beschreibungstext für den Hero-Bereich. Hier kannst du die wichtigsten Informationen kurz zusammenfassen.</p>
  <a href="#" class="btn btn-primary" style="background: white; color: #667eea;">Mehr erfahren</a>
</section>',
                'css' => '',
                'js' => '',
            ],
            [
                'name' => 'grid',
                'title' => '3er Grid',
                'description' => 'Ein 3-spaltiges Grid für Inhalte.',
                'tags' => ['layout', 'grid', 'content'],
                'content' => '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin: 1.5rem 0;">
  <div style="padding: 1rem; background: #f5f5f5; border-radius: 0.5rem;">
    <h3 style="margin: 0 0 0.5rem; font-weight: 600;">Element 1</h3>
    <p style="margin: 0; font-size: 0.9rem;">Kurzer Text für dieses Element.</p>
  </div>
  <div style="padding: 1rem; background: #f5f5f5; border-radius: 0.5rem;">
    <h3 style="margin: 0 0 0.5rem; font-weight: 600;">Element 2</h3>
    <p style="margin: 0; font-size: 0.9rem;">Kurzer Text für dieses Element.</p>
  </div>
  <div style="padding: 1rem; background: #f5f5f5; border-radius: 0.5rem;">
    <h3 style="margin: 0 0 0.5rem; font-weight: 600;">Element 3</h3>
    <p style="margin: 0; font-size: 0.9rem;">Kurzer Text für dieses Element.</p>
  </div>
</div>',
                'css' => '',
                'js' => '',
            ],
            [
                'name' => 'testimonial',
                'title' => 'Kundenzitat / Testimonial',
                'description' => 'Ein Kundenbewertung mit Zitat, Name und Bild.',
                'tags' => ['social-proof', 'marketing', 'quote'],
                'content' => '<div style="padding: 1.5rem; border-left: 4px solid #667eea; background: #f9f9f9; border-radius: 0.5rem;">
  <blockquote style="margin: 0 0 1rem; font-style: italic; font-size: 1.05rem; color: #333;">"Das ist ein großartiges Kundenzitat. Es kann von einem zufriedenen Kunden kommen und die Vorteile hervorheben."</blockquote>
  <div style="display: flex; align-items: center; gap: 0.75rem;">
    <div style="width: 40px; height: 40px; border-radius: 50%; background: #ddd;"></div>
    <div>
      <p style="margin: 0; font-weight: 600; font-size: 0.95rem;">John Doe</p>
      <p style="margin: 0; font-size: 0.85rem; color: #666;">CEO bei Beispiel GmbH</p>
    </div>
  </div>
</div>',
                'css' => '',
                'js' => '',
            ],
            [
                'name' => 'cta-section',
                'title' => 'Call-to-Action Bereich',
                'description' => 'Ein großer CTA-Bereich mit Heading und Button.',
              'tags' => ['cta', 'marketing', 'conversion'],
                'content' => '<section style="padding: 2.5rem 2rem; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 1rem; text-align: center;">
  <h2 style="margin: 0 0 1rem; font-size: 1.8rem; font-weight: 700;">Bereit für den nächsten Schritt?</h2>
  <p style="margin: 0 0 1.5rem; font-size: 1rem; opacity: 0.95;">Mach jetzt den ersten Schritt und entdecke, wie wir dir helfen können.</p>
  <a href="#" class="btn btn-primary" style="background: white; color: #f5576c; padding: 0.75rem 2rem; font-weight: 600;">Jetzt starten</a>
</section>',
                'css' => '',
                'js' => '',
            ],
            [
                'name' => 'accordion',
                'title' => 'Akkordeon / Accordion',
                'description' => 'Ein Akkordeon für Fragen und Antworten.',
                'tags' => ['faq', 'content', 'interactive'],
                'content' => '<div style="border: 1px solid #e0e0e0; border-radius: 0.5rem; overflow: hidden;">
  <details style="padding: 1rem; border-bottom: 1px solid #e0e0e0; cursor: pointer;">
    <summary style="font-weight: 600; cursor: pointer;">Frage 1: Was ist ein Akkordeon?</summary>
    <p style="margin: 0.75rem 0 0;">Antworttext hier. Dies ist eine häufig gestellte Frage mit der entsprechenden Antwort.</p>
  </details>
  <details style="padding: 1rem; border-bottom: 1px solid #e0e0e0; cursor: pointer;">
    <summary style="font-weight: 600; cursor: pointer;">Frage 2: Wie wird es verwendet?</summary>
    <p style="margin: 0.75rem 0 0;">Antworttext hier. Erklären Sie, wie diese Komponente in Ihrem Projekt verwendet wird.</p>
  </details>
  <details style="padding: 1rem; cursor: pointer;">
    <summary style="font-weight: 600; cursor: pointer;">Frage 3: Kann man es anpassen?</summary>
    <p style="margin: 0.75rem 0 0;">Ja, natürlich können Sie diese Komponente nach Ihren Bedürfnissen anpassen.</p>
  </details>
</div>',
                'css' => '',
                'js' => '',
            ],
        ];

        foreach ($components as $data) {
            ContentComponent::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
