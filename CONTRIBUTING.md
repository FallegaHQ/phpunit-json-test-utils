# Contributing to JSON Test Utilities for PHPUnit

Thank you for considering contributing to JSON Test Utilities! This document outlines the process for contributing to the project and helps ensure your contributions align with the project's standards.

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to the project maintainers.

## How Can I Contribute?

### Reporting Bugs

Before submitting a bug report:
- Check the issue tracker to see if the problem has already been reported
- Make sure you're using the latest version of the package
- Determine if the bug is in the package or in your implementation

When submitting a bug report, include:
- A clear and descriptive title
- A detailed description of the issue
- Steps to reproduce the behavior
- Expected behavior versus actual behavior
- PHP and PHPUnit versions you're using
- Code samples that demonstrate the issue

### Suggesting Enhancements

Enhancement suggestions are always welcome! Include:
- A clear and descriptive title
- A detailed description of the proposed feature
- An explanation of why this enhancement would be useful
- Code examples of how the feature would be used
- Any relevant documentation or references

### Pull Requests

1. **Fork the repository**
2. **Create a branch** from `main` for your feature or fix
3. **Write tests** to verify your changes
4. **Update documentation** if necessary
5. **Ensure code style compliance** with PSR-12
6. **Submit a pull request** targeting the `main` branch

## Development Workflow

### Setting Up the Development Environment

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/phpunit-json-test-utils.git
cd phpunit-json-test-utils

# Install dependencies
composer install
```

### Running Tests

Before submitting a PR, make sure all tests pass:

```bash
# Run the test suite
composer test

# Run code style checks
composer check-style
```

### Coding Standards

This project follows PSR-12 coding standards. You can check your code with:

```bash
composer check-style
```

And automatically fix many issues with:

```bash
composer fix-style
```

## Pull Request Guidelines

- Keep pull requests focused on a single topic
- Follow the project's code style
- Include tests for new features or bug fixes
- Update documentation as necessary
- Keep a clean commit history - use rebase if necessary
- Reference issues in commit messages and PR descriptions

## Documentation

Good documentation is essential. When adding new features, please:

- Update the README.md if necessary
- Add PHPDoc blocks to all public methods
- Include examples of how to use the new feature
- Update any relevant documentation files

## Release Process

Project maintainers will handle the release process, including:
- Version number updates following semantic versioning
- Release notes compilation
- Package publication

## Questions?

If you have any questions about contributing, please reach out to the maintainers or open an issue for discussion.

Thank you for contributing to JSON Test Utilities!
