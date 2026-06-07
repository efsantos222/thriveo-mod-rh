<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema ZBB - Orçamento Base Zero</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-
awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #2c3e50, #4a6491);
            color: white;
            padding: 25px 0;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        h1 {
            font-size: 2.2rem;
            margin-bottom: 5px;
        }
        
        .subtitle {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .total-budget {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 15px 25px;
            border-radius: 8px;
            text-align: center;
        }
        
        .total-budget h3 {
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        
        .total-amount {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            font-size: 1.4rem;
            color: #2c3e50;
        }
        
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .btn-add {
            background-color: #2ecc71;
        }
        
        .btn-add:hover {
            background-color: #27ae60;
        }
        
        .btn-edit {
            background-color: #f39c12;
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        
        .btn-delete {
            background-color: #e74c3c;
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #3498db;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #eee;
            color: #2c3e50;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .amount {
            font-weight: 600;
        }
        
        .income {
            color: #2ecc71;
        }
        
        .expense {
            color: #e74c3c;
        }
        
        .progress-bar {
            height: 10px;
            background-color: #ecf0f1;
            border-radius: 5px;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .progress {
            height: 100%;
            background-color: #3498db;
            border-radius: 5px;
        }
        
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close {
            font-size: 1.8rem;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .close:hover {
            color: #333;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .total-budget {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <div>
                    <h1><i class="fas fa-chart-pie"></i> Sistema ZBB</h1>
                    <p class="subtitle">Orçamento Base Zero - Controle
Financeiro Personalizado</p>
                </div>
                <div class="total-budget">
                    <h3>Saldo Disponível</h3>
                    <div class="total-amount" id="totalBalance">R$ 0,00</div>
                </div>
            </div>
        </header>
        
        <div class="alert" id="alertMessage"></div>
        
        <div class="dashboard">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Categorias de Orçamento</h2>
                    <button class="btn btn-add" id="addCategoryBtn">
                        <i class="fas fa-plus"></i> Nova Categoria
                    </
