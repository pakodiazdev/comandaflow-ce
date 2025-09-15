describe('Full Stack Integration Tests', () => {
  it('should verify that Laravel API is accessible', () => {
    // Test that Laravel API is running
    cy.request('http://localhost/test.php')
      .then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.include('Laravel')
      })
  })

  it('should verify PostgreSQL connection through Laravel', () => {
    // Test database connection through Laravel
    cy.request('http://localhost/test-db.php')
      .then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body).to.include('PostgreSQL')
        expect(response.body).to.include('Conexión Laravel DB exitosa')
      })
  })

  it('should verify that React app can potentially communicate with Laravel API', () => {
    // Visit React app
    cy.visit('/')
    
    // Verify React app loads
    cy.get('#root').should('exist')
    
    // Make API call from React app context (future implementation)
    cy.window().then((win) => {
      // This would be where you test actual API calls from React
      // For now, just verify that fetch is available
      expect(win.fetch).to.be.a('function')
    })
  })

  it('should verify development environment health', () => {
    const services = [
      { name: 'React App', url: 'http://localhost:3000' },
      { name: 'Laravel API', url: 'http://localhost' },
      { name: 'pgAdmin', url: 'http://localhost:5050' },
      { name: 'VS Code Web', url: 'http://localhost:8080' }
    ]

    services.forEach((service) => {
      if (service.name === 'React App') {
        // For React app, visit normally
        cy.visit('/')
        cy.get('#root').should('exist')
      } else {
        // For other services, just check they respond
        cy.request({
          url: service.url,
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).to.be.oneOf([200, 302, 401]) // Some services might redirect or require auth
        })
      }
    })
  })
})