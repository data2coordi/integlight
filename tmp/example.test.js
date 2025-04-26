// tests/js/unit/example.test.js

// 簡単な関数のテスト例
function sum(a, b) {
    return a + b;
}

describe('Example Tests', () => {
    it('should return the correct sum', () => {
        expect(sum(1, 2)).toBe(3);
    });

    // 必要に応じて他のテストケースを追加
});

// 実際のファイルから関数をインポートしてテストする場合
// import { myFunction } from '../../js/my-feature';
// describe('My Feature', () => {
//   it('should do something correctly', () => {
//     expect(myFunction()).toEqual('expected result');
//   });
// });
