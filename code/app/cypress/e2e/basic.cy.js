describe('Basic Cypress Test', () => {
  it('should run a basic test without server dependency', () => {
    // Test básico sin dependencia del servidor
    cy.log('Cypress está funcionando correctamente')
    
    // Test de funcionalidades básicas de Cypress
    const testData = { name: 'Test User', age: 25 }
    expect(testData.name).to.equal('Test User')
    expect(testData.age).to.be.a('number')
    
    // Test de manipulación de datos
    const numbers = [1, 2, 3, 4, 5]
    const sum = numbers.reduce((acc, num) => acc + num, 0)
    expect(sum).to.equal(15)
    
    cy.log('✅ Test básico completado exitosamente')
  })

  it('should test external website access', () => {
    // Test con sitio externo para verificar conectividad
    cy.visit('https://example.com')
    cy.contains('Example Domain')
    cy.get('h1').should('contain', 'Example Domain')
    
    cy.log('✅ Test de conectividad externa completado')
  })
})