export default function Footer() {
  const currentYear = new Date().getFullYear()

  return (
    <footer className="w-full border-t border-border bg-card py-12 text-text-secondary">
      <div className="mx-auto max-w-7xl px-4 md:px-8">
        <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
          {/* Brand and Policy */}
          <div className="flex flex-col gap-3">
            <h3 className="font-heading text-xl font-bold text-primary">Raj Confections</h3>
            <p className="font-sans text-sm leading-relaxed max-w-xs">
              Pure homemade cakes made with absolute hygiene. No added preservatives or harmful colors. 100% eggless, gluten-free, and lactose-free cream options available.
            </p>
          </div>

          {/* Contact Details */}
          <div className="flex flex-col gap-3">
            <h4 className="font-sans text-sm font-semibold uppercase tracking-wider text-text-primary">Contact & Orders</h4>
            <ul className="flex flex-col gap-2 font-sans text-sm">
              <li>
                <span className="font-medium text-text-primary">Phone: </span>
                <a href="tel:+919477489551" className="hover:text-primary transition-colors">+91 94774 89551</a>
              </li>
              <li>
                <span className="font-medium text-text-primary">Phone 2: </span>
                <a href="tel:+919432365368" className="hover:text-primary transition-colors">+91 94323 65368</a>
              </li>
              <li>
                <span className="font-medium text-text-primary font-sans text-xs block mt-1 text-muted-foreground">
                  * Please place custom orders at least 3 days in advance.
                </span>
              </li>
            </ul>
          </div>

          {/* Operational Hours */}
          <div className="flex flex-col gap-3">
            <h4 className="font-sans text-sm font-semibold uppercase tracking-wider text-text-primary">Operational Hours</h4>
            <ul className="flex flex-col gap-1 font-sans text-sm">
              <li>Monday - Sunday</li>
              <li className="text-text-primary font-medium">10:00 AM - 8:00 PM</li>
              <li className="mt-2 text-xs text-muted-foreground">
                Kolkata, West Bengal, India
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="mt-12 border-t border-border pt-6 text-center font-sans text-xs">
          <p>&copy; {currentYear} Raj Confections. All rights reserved. Happiness Homemade.</p>
        </div>
      </div>
    </footer>
  )
}
