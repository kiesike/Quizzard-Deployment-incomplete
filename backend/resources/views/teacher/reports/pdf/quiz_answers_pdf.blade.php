<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .page {
            padding: 40px 50px;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 12px;
        }
        .header h1 {
            font-size: 20pt;
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        .header p {
            font-size: 11pt;
            color: #64748b;
            margin: 0;
            font-style: italic;
        }
        .question {
            margin-bottom: 24px;
        }
        .question-text {
            font-weight: bold;
            margin-bottom: 8px;
        }
        .points {
            font-weight: normal;
            font-size: 10pt;
            color: #64748b;
        }
        .answer-box {
            margin-left: 24px;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 6px 12px;
            display: inline-block;
            color: #15803d;
            font-weight: bold;
            font-size: 11pt;
        }
        .matching-table {
            width: 90%;
            border-collapse: collapse;
            margin-left: 24px;
            margin-top: 4px;
        }
        .matching-table th {
            background-color: #f8fafc;
            font-size: 10pt;
            text-transform: uppercase;
            color: #64748b;
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .matching-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11pt;
        }
        .match-answer {
            color: #15803d;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page">

        <div class="header">
            <h1>{{ $quiz->title }}</h1>
            <p>Answer Key</p>
            @if ($quiz->description)
                <p style="margin-top: 4px; font-style: normal; color: #475569;">{{ $quiz->description }}</p>
            @endif
        </div>

        @foreach ($quiz->questions->sortBy('order') as $index => $question)
            <div class="question">
                <div class="question-text">
                    {{ $index + 1 }}.
                    {{ $question->question_text }}
                    <span class="points">({{ $question->points }} {{ $question->points == 1 ? 'pt' : 'pts' }})</span>
                </div>

                @if ($question->question_type === 'multiple_choice')
                    @php $correct = $question->answerOptions->firstWhere('is_correct', true); @endphp
                    <div class="answer-box">✓ {{ $correct?->option_text ?? '—' }}</div>

                @elseif ($question->question_type === 'true_false')
                    @php $correct = $question->answerOptions->firstWhere('is_correct', true); @endphp
                    <div class="answer-box">✓ {{ $correct?->option_text ?? '—' }}</div>

                @elseif ($question->question_type === 'identification')
                    @php $correct = $question->answerOptions->first(); @endphp
                    <div class="answer-box">✓ {{ $correct?->option_text ?? '—' }}</div>

                @elseif ($question->question_type === 'matching')
                    <table class="matching-table">
                        <thead>
                            <tr>
                                <th>Premise</th>
                                <th>Correct Match</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($question->answerOptions->sortBy('order') as $pair)
                                <tr>
                                    <td>{{ $pair->option_text }}</td>
                                    <td class="match-answer">{{ $pair->match_pair }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endforeach

    </div>
</body>
</html>