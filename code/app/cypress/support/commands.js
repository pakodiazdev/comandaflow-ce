// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

// Custom command for login (example for future use)
Cypress.Commands.add('login', (email, password) => {
  cy.session([email, password], () => {
    cy.visit('/login')
    cy.get('[data-cy=email]').type(email)
    cy.get('[data-cy=password]').type(password)
    cy.get('[data-cy=login-button]').click()
    cy.url().should('not.include', '/login')
  })
})

// Custom command to check React app is loaded
Cypress.Commands.add('checkReactAppLoaded', () => {
  cy.get('[data-reactroot], #root').should('exist')
  cy.get('.react-logo, [data-cy=app-loaded]').should('be.visible')
})

// Custom command to wait for API responses
Cypress.Commands.add('waitForAPI', (alias) => {
  cy.wait(alias).its('response.statusCode').should('eq', 200)
})

// Custom command to check responsive design
Cypress.Commands.add('checkResponsive', () => {
  // Desktop
  cy.viewport(1280, 720)
  cy.get('body').should('be.visible')
  
  // Tablet
  cy.viewport(768, 1024)
  cy.get('body').should('be.visible')
  
  // Mobile
  cy.viewport(375, 667)
  cy.get('body').should('be.visible')
})