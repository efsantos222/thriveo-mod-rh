"""
DISC Assessment System

A comprehensive Python-based DISC personality assessment system.

This package provides tools to:
- Conduct DISC personality assessments
- Calculate and analyze results
- Generate detailed profile reports
- Export results in multiple formats

Main components:
- disc_test: Test administration and flow
- disc_data: Questions and profile descriptions
- disc_results: Result processing and reporting
- main: Command-line interface

Usage:
    from disc_assessment import DISCTest, DISCResults

    test = DISCTest()
    results = test.run_test()
    results.display_results()

Version: 1.0.0
Author: PromptMaster Development Team
"""

__version__ = "1.0.0"
__author__ = "PromptMaster Development Team"

from .disc_test import DISCTest, InteractiveDISCTest
from .disc_results import DISCResults
from .disc_data import DISC_QUESTIONS, PROFILE_DESCRIPTIONS

__all__ = [
    'DISCTest',
    'InteractiveDISCTest',
    'DISCResults',
    'DISC_QUESTIONS',
    'PROFILE_DESCRIPTIONS'
]
