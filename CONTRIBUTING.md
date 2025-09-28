# Contributing to grim-reapper PDF Services PHP SDK

Thank you for your interest in contributing to the grim-reapper PDF Services PHP SDK! We welcome contributions from the community to help improve and maintain this project.

## 🛠️ Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- Git

### Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/your-username/pdf-services-php.git
   cd pdf-services-php
   ```

3. **Install dependencies**:
   ```bash
   composer install
   ```

4. **Run tests** to ensure everything works:
   ```bash
   composer test
   ```

5. **Create a feature branch**:
   ```bash
   git checkout -b feature/your-feature-name
   ```

## 📋 Contribution Guidelines

### 🐛 Reporting Issues

When reporting issues, please include:

- **Clear title** describing the problem
- **Steps to reproduce** the issue
- **Expected behavior** vs. actual behavior
- **Environment details**:
  - PHP version: `php --version`
  - OS version
  - Package version: `composer show grim-reapper/pdf-services-php`
- **Relevant code snippets**
- **Error messages** or stack traces

### ✨ Feature Requests

Feature requests should include:

- **Clear description** of the proposed feature
- **Use case** and why it's needed
- **Implementation ideas** if you have any
- **Potential breaking changes** if applicable

### 🛠️ Code Contributions

#### Code Style

This project follows:

- **PSR-12** coding standards
- **PSR-4** autoloading
- **Strict typing** where possible
- **Comprehensive documentation** with PHPDoc

#### Running Quality Checks

```bash
# Run linting
composer lint

# Run static analysis
composer analyze

# Run tests with coverage
composer test:coverage

# Fix code style issues
composer lint:fix
```

#### Commit Message Format

Please use descriptive commit messages:

```bash
# Good
git commit -m "Add support for custom HTTP headers in requests"

# Bad
git commit -m "fix bug"
```

#### Testing

- **Write tests** for new features and bug fixes
- **Maintain test coverage** above 80%
- **Unit tests** for all classes and methods
- **Integration tests** for external API interactions

### 📖 Documentation

- **Update documentation** for new features
- **Keep examples up-to-date**
- **Include code comments** and PHPDoc blocks
- **Update README.md** if needed

## 🚀 Pull Request Process

1. **Ensure all tests pass**:
   ```bash
   composer test
   ```

2. **Run quality checks**:
   ```bash
   composer lint && composer analyze
   ```

3. **Update documentation** if needed

4. **Create a Pull Request**:
   - Use a descriptive title
   - Reference any related issues
   - Provide a clear description of changes
   - Include screenshots for UI changes
   - Ensure CI checks pass

5. **Wait for review** and address any feedback

## 📚 Project Structure

```
grim-reapper/pdf-services-php/
├── src/
│   ├── GrimReapper/
│   │   ├── PdfServices/        # Main SDK classes
│   │   │   ├── Client.php      # Main client class
│   │   │   ├── Config/         # Configuration classes
│   │   │   ├── Models/         # Data model classes
│   │   │   ├── Services/       # Service implementation
│   │   │   ├── Exceptions/     # Exception classes
│   │   │   ├── Http/           # HTTP utilities
│   │   │   └── Utils/          # Utility classes
│   │   └── Contracts/          # Interface contracts
├── tests/                      # Unit and integration tests
├── docs/                       # Documentation
├── examples/                   # Usage examples
├── composer.json               # Dependency management
├── README.md                   # Main documentation
└── phpunit.xml                 # Test configuration
```

## 🔧 Development Workflow

### Setting up Environment Variables for Testing

```bash
# Create a test environment file (not committed to git)
cp .env.example .env

# Edit with your Adobe PDF Services credentials for integration testing
export GRIM_REAPPER_PDF_SERVICES_API_KEY="your-test-key"
export GRIM_REAPPER_PDF_SERVICES_CLIENT_ID="your-test-client-id"
export GRIM_REAPPER_PDF_SERVICES_ORGANIZATION_ID="your-test-org-id"
```

### Working with Adobe PDF Services API

When contributing features that interact with the Adobe API:

1. **Use test credentials** for development
2. **Mock external API calls** in unit tests
3. **Document API limitations** and rate limits
4. **Handle error cases** gracefully

### Adding New Services

When adding new PDF service functionality:

1. **Extend `AbstractService`** or implement `ServiceInterface`
2. **Add comprehensive tests** for the service
3. **Update the `Client` class** to include the new service
4. **Add documentation** and usage examples
5. **Update API reference** documentation

### Namespace Conventions

- **Main classes**: `GrimReapper\PdfServices\...`
- **Tests**: `GrimReapper\PdfServices\Tests\...`
- **Contracts**: `GrimReapper\Contracts\...`

## 🎯 Code Review Process

### Checklist for Reviewers

- [ ] **Code Quality**: Follows PSR standards and project conventions
- [ ] **Tests**: Adequate test coverage and passing tests
- [ ] **Documentation**: Updated and accurate
- [ ] **Security**: No security vulnerabilities
- [ ] **Performance**: Efficient implementation
- [ ] **Compatibility**: Maintains backward compatibility

### Checklist for Contributors

- [ ] **Self-review** your code before submission
- [ ] **Test thoroughly** on multiple environments
- [ ] **Update documentation** as needed
- [ ] **Follow coding standards**
- [ ] **Handle edge cases** and error scenarios
- [ ] **Maintain backward compatibility** unless breaking changes are justified

## 📞 Getting Help

- **For contribution questions**: Open a [GitHub Discussion](https://github.com/grim-reapper/pdf-services-php/discussions)
- **For technical issues**: Check [existing issues](https://github.com/grim-reapper/pdf-services-php/issues) and open new ones if needed
- **For security concerns**: Email `security@grim-reapper.com`

## 📄 License

By contributing to this project, you agree that your contributions will be licensed under the same MIT License that covers the project.

## 🙏 Thank You

Thank you for taking the time to contribute to the grim-reapper PDF Services PHP SDK! Your contributions help make this a better tool for the PHP community.

---

*© 2024 grim-reapper. grim-reapper PDF Services PHP SDK contribution guidelines.*
