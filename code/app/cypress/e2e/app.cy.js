describe('React App - Basic E2E Tests', () => {
  beforeEach(() => {
    // Visit the home page before each test
    cy.visit('/')
  })

  it('should load the React application successfully', () => {
    // Check that the page title contains "React"
    cy.title().should('include', 'Vite + React')
    
    // Check that the root element exists
    cy.get('#root').should('exist')
    
    // Check that main content is visible
    cy.get('#root').should('be.visible')
  })

  it('should display the Vite and React logos', () => {
    // Check for Vite logo
    cy.get('img[alt="Vite logo"]').should('be.visible')
    
    // Check for React logo
    cy.get('img[alt="React logo"]').should('be.visible')
    
    // Check that React logo is spinning (has animation)
    cy.get('.logo.react')
      .should('have.class', 'react')
      .and('be.visible')
  })

  it('should display the main heading and content', () => {
    // Check main heading
    cy.contains('h1', 'Vite + React').should('be.visible')
    
    // Check that there's a counter button
    cy.get('button').contains('count is').should('be.visible')
    
    // Check that there's instructional text
    cy.contains('Edit src/App.jsx and save to test HMR').should('be.visible')
  })

  it('should interact with the counter button', () => {
    // Get the counter button
    cy.get('button').contains('count is').as('counterButton')
    
    // Check initial state
    cy.get('@counterButton').should('contain', 'count is 0')
    
    // Click the button
    cy.get('@counterButton').click()
    
    // Check that count increased
    cy.get('@counterButton').should('contain', 'count is 1')
    
    // Click multiple times
    cy.get('@counterButton').click().click().click()
    
    // Check final count
    cy.get('@counterButton').should('contain', 'count is 4')
  })

  it('should have working links', () => {
    // Check that Vite logo links to Vite website
    cy.get('a[href="https://vite.dev"]')
      .should('have.attr', 'target', '_blank')
      .and('be.visible')
    
    // Check that React logo links to React website
    cy.get('a[href="https://react.dev"]')
      .should('have.attr', 'target', '_blank')
      .and('be.visible')
  })

  it('should be responsive on different screen sizes', () => {
    // Test desktop view
    cy.viewport(1280, 720)
    cy.get('#root').should('be.visible')
    cy.get('.logo').should('be.visible')
    
    // Test tablet view
    cy.viewport(768, 1024)
    cy.get('#root').should('be.visible')
    cy.get('.logo').should('be.visible')
    
    // Test mobile view
    cy.viewport(375, 667)
    cy.get('#root').should('be.visible')
    cy.get('.logo').should('be.visible')
  })

  it('should have proper page structure and accessibility', () => {
    // Check that page has proper structure
    cy.get('body').should('exist')
    cy.get('#root').should('exist')
    
    // Check for proper heading structure
    cy.get('h1').should('exist').and('be.visible')
    
    // Check that images have alt text
    cy.get('img').each(($img) => {
      cy.wrap($img).should('have.attr', 'alt')
    })
    
    // Check that links have href attributes
    cy.get('a').each(($link) => {
      cy.wrap($link).should('have.attr', 'href')
    })
  })
})