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
        .options {
            margin-left: 24px;
        }
        .option {
            margin-bottom: 4px;
        }
        .blank {
            margin-left: 24px;
            color: #94a3b8;
        }
        .matching-table {
            width: 100%;
            border-collapse: collapse;
            margin-left: 24px;
        }
        .matching-table td {
            padding: 4px 8px;
            font-size: 11pt;
        }
        .matching-center {
            text-align: center;
            color: #94a3b8;
            width: 30px;
        }
    </style>
</head>
<body>
    <div class="page">

        <div class="header">
            <h1>{{ $quiz->title }}</h1>
            <p>Test Questionnaire</p>
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
                    @php $letters = ['A', 'B', 'C', 'D']; @endphp
                    <div class="options">
                        @foreach ($question->answerOptions->sortBy('order') as $i => $option)
                            <div class="option">{{ $letters[$i] ?? chr(65 + $i) }}. {{ $option->option_text }}</div>
                        @endforeach
                    </div>

                @elseif ($question->question_type === 'true_false')
                    <div class="options">
                        <div class="option">A. True</div>
                        <div class="option">B. False</div>
                    </div>

                @elseif ($question->question_type === 'identification')
                    <div class="blank">Answer: ___________________________</div>

                @elseif ($question->question_type === 'matching')
                    <table class="matching-table">
                        @foreach ($question->answerOptions->sortBy('order') as $pair)
                            <tr>
                                <td style="width: 45%;">{{ $pair->option_text }}</td>
                                <td style="width: 45%;">{{ $pair->match_pair }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </div>
        @endforeach

    </div>
</body>
</html>