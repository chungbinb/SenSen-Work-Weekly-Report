/**
 * 中国农历（阴阳历）和西元阳历即公历互转JavaScript库
 * 简化版本 - 基于 jjonline/calendar.js
 */
const calendar = {
    // 农历1900-3000的闰大小信息表
    lunarInfo: [0x04bd8, 0x04ae0, 0x0a570, 0x054d5, 0x0d260, 0x0d950, 0x16554, 0x056a0, 0x09ad0, 0x055d2,//1900-1909
        0x04ae0, 0x0a5b6, 0x0a4d0, 0x0d250, 0x1d255, 0x0b540, 0x0d6a0, 0x0ada2, 0x095b0, 0x14977,//1910-1919
        0x04970, 0x0a4b0, 0x0b4b5, 0x06a50, 0x06d40, 0x1ab54, 0x02b60, 0x09570, 0x052f2, 0x04970,//1920-1929
        0x06566, 0x0d4a0, 0x0ea50, 0x16a95, 0x05ad0, 0x02b60, 0x186e3, 0x092e0, 0x1c8d7, 0x0c950,//1930-1939
        0x0d4a0, 0x1d8a6, 0x0b550, 0x056a0, 0x1a5b4, 0x025d0, 0x092d0, 0x0d2b2, 0x0a950, 0x0b557,//1940-1949
        0x06ca0, 0x0b550, 0x15355, 0x04da0, 0x0a5b0, 0x14573, 0x052b0, 0x0a9a8, 0x0e950, 0x06aa0,//1950-1959
        0x0aea6, 0x0ab50, 0x04b60, 0x0aae4, 0x0a570, 0x05260, 0x0f263, 0x0d950, 0x05b57, 0x056a0,//1960-1969
        0x096d0, 0x04dd5, 0x04ad0, 0x0a4d0, 0x0d4d4, 0x0d250, 0x0d558, 0x0b540, 0x0b6a0, 0x195a6,//1970-1979
        0x095b0, 0x049b0, 0x0a974, 0x0a4b0, 0x0b27a, 0x06a50, 0x06d40, 0x0af46, 0x0ab60, 0x09570,//1980-1989
        0x04af5, 0x04970, 0x064b0, 0x074a3, 0x0ea50, 0x06b58, 0x05ac0, 0x0ab60, 0x096d5, 0x092e0,//1990-1999
        0x0c960, 0x0d954, 0x0d4a0, 0x0da50, 0x07552, 0x056a0, 0x0abb7, 0x025d0, 0x092d0, 0x0cab5,//2000-2009
        0x0a950, 0x0b4a0, 0x0baa4, 0x0ad50, 0x055d9, 0x04ba0, 0x0a5b0, 0x15176, 0x052b0, 0x0a930,//2010-2019
        0x07954, 0x06aa0, 0x0ad50, 0x05b52, 0x04b60, 0x0a6e6, 0x0a4e0, 0x0d260, 0x0ea65, 0x0d530,//2020-2029
        0x05aa0, 0x076a3, 0x096d0, 0x04afb, 0x04ad0, 0x0a4d0, 0x1d0b6, 0x0d250, 0x0d520, 0x0dd45,//2030-2039
        0x0b5a0, 0x056d0, 0x055b2, 0x049b0, 0x0a577, 0x0a4b0, 0x0aa50, 0x1b255, 0x06d20, 0x0ada0,//2040-2049
        0x14b63, 0x09370, 0x049f8, 0x04970, 0x064b0, 0x168a6, 0x0ea50, 0x06aa0, 0x1a6c4, 0x0aae0,//2050-2059
        0x092e0, 0x0d2e3, 0x0c960, 0x0d557, 0x0d4a0, 0x0da50, 0x05d55, 0x056a0, 0x0a6d0, 0x055d4,//2060-2069
        0x052d0, 0x0a9b8, 0x0a950, 0x0b4a0, 0x0b6a6, 0x0ad50, 0x055a0, 0x0aba4, 0x0a5b0, 0x052b0,//2070-2079
        0x0b273, 0x06930, 0x07337, 0x06aa0, 0x0ad50, 0x14b55, 0x04b60, 0x0a570, 0x054e4, 0x0d160,//2080-2089
        0x0e968, 0x0d520, 0x0daa0, 0x16aa6, 0x056d0, 0x04ae0, 0x0a9d4, 0x0a2d0, 0x0d150, 0x0f252,//2090-2099
        0x0d520, 0x0db27, 0x0b5a0, 0x055d0, 0x04db5, 0x049b0, 0x0a4b0, 0x0d4b4, 0x0aa50, 0x0b559,//2100-2109
    ],

    // 公历每个月份的天数普通表
    solarMonth: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],

    // 天干地支之天干速查表
    Gan: ["\u7532", "\u4e59", "\u4e19", "\u4e01", "\u620a", "\u5df1", "\u5e9a", "\u8f9b", "\u58ec", "\u7678"],

    // 天干地支之地支速查表
    Zhi: ["\u5b50", "\u4e11", "\u5bc5", "\u536f", "\u8fb0", "\u5df3", "\u5348", "\u672a", "\u7533", "\u9149", "\u620c", "\u4ea5"],

    // 生肖
    Animals: ["\u9f20", "\u725b", "\u864e", "\u5154", "\u9f99", "\u86c7", "\u9a6c", "\u7f8a", "\u7334", "\u9e21", "\u72d7", "\u732a"],

    // 农历日期中文显示
    nStr1: ["\u65e5", "\u4e00", "\u4e8c", "\u4e09", "\u56db", "\u4e94", "\u516d", "\u4e03", "\u516b", "\u4e5d", "\u5341"],
    nStr2: ["\u521d", "\u5341", "\u5eff", "\u5345"],
    nStr3: ["\u6b63", "\u4e8c", "\u4e09", "\u56db", "\u4e94", "\u516d", "\u4e03", "\u516b", "\u4e5d", "\u5341", "\u51ac", "\u814a"],

    // 公历节日
    solarFestival: {
        '1-1': '元旦',
        '2-14': '情人节',
        '3-8': '妇女节',
        '3-12': '植树节',
        '4-1': '愚人节',
        '5-1': '劳动节',
        '5-4': '青年节',
        '6-1': '儿童节',
        '7-1': '建党节',
        '8-1': '建军节',
        '9-10': '教师节',
        '10-1': '国庆节',
        '12-25': '圣诞节'
    },

    // 农历节日
    lunarFestival: {
        '1-1': '春节',
        '1-15': '元宵节',
        '2-2': '龙抬头',
        '5-5': '端午节',
        '7-7': '七夕节',
        '7-15': '中元节',
        '8-15': '中秋节',
        '9-9': '重阳节',
        '12-8': '腊八节',
        '12-23': '小年',
        '12-29': '除夕', // 小月除夕
        '12-30': '除夕'  // 大月除夕
    },

    /**
     * 返回农历y年一整年的总天数
     */
    lYearDays: function (y) {
        let i, sum = 348;
        for (i = 0x8000; i > 0x8; i >>= 1) {
            sum += (this.lunarInfo[y - 1900] & i) ? 1 : 0;
        }
        return (sum + this.leapDays(y));
    },

    /**
     * 返回农历y年闰月是哪个月；若y年没有闰月 则返回0
     */
    leapMonth: function (y) {
        return (this.lunarInfo[y - 1900] & 0xf);
    },

    /**
     * 返回农历y年闰月的天数 若该年没有闰月则返回0
     */
    leapDays: function (y) {
        if (this.leapMonth(y)) {
            return (this.lunarInfo[y - 1900] & 0x10000) ? 30 : 29;
        }
        return 0;
    },

    /**
     * 返回农历y年m月（非闰月）的总天数，计算m为闰月时的天数请使用leapDays方法
     */
    monthDays: function (y, m) {
        if (m > 12 || m < 1) { return -1 }
        return (this.lunarInfo[y - 1900] & (0x10000 >> m)) ? 30 : 29;
    },

    /**
     * 农历年份转换为干支纪年
     */
    toGanZhiYear: function (lYear) {
        let ganKey = (lYear - 3) % 10;
        let zhiKey = (lYear - 3) % 12;
        if (ganKey === 0) ganKey = 10;
        if (zhiKey === 0) zhiKey = 12;
        return this.Gan[ganKey - 1] + this.Zhi[zhiKey - 1];
    },

    /**
     * 公历月、日判断所属星座
     */
    toAstro: function (cMonth, cDay) {
        const s = "\u9b54\u7faf\u6c34\u74f6\u53cc\u9c7c\u767d\u7f8a\u91d1\u725b\u53cc\u5b50\u5de8\u87f9\u72ee\u5b50\u5904\u5973\u5929\u79e4\u5929\u874e\u5c04\u624b\u9b54\u7faf";
        const arr = [20, 19, 21, 21, 21, 22, 23, 23, 23, 23, 22, 22];
        return s.substr(cMonth * 2 - (cDay < arr[cMonth - 1] ? 2 : 0), 2) + "\u5ea7";
    },

    /**
     * 传入offset偏移量返回干支
     */
    toGanZhi: function (offset) {
        return this.Gan[offset % 10] + this.Zhi[offset % 12];
    },

    /**
     * 传入农历数字月份返回汉语通俗表示法
     */
    toChinaMonth: function (m) {
        if (m > 12 || m < 1) { return -1 }
        let s = this.nStr3[m - 1];
        s += "\u6708";
        return s;
    },

    /**
     * 传入农历日期数字返回汉字表示法
     */
    toChinaDay: function (d) {
        let s;
        switch (d) {
            case 10:
                s = '\u521d\u5341'; break;
            case 20:
                s = '\u4e8c\u5341'; break;
            case 30:
                s = '\u4e09\u5341'; break;
            default:
                s = this.nStr2[Math.floor(d / 10)];
                s += this.nStr1[d % 10];
        }
        return s;
    },

    /**
     * 年份转生肖[!仅能大致转换] => 精确划分生肖分界线是"立春"
     */
    getAnimal: function (y) {
        return this.Animals[(y - 4) % 12]
    },

    /**
     * 传入阳历年月日获得详细的公历、农历object信息 <=>JSON
     * !important! 公历参数区间1900.1.31~2100.12.31
     */
    solar2lunar: function (y, m, d) {
        // 参数区间1900.1.31~2100.12.31
        if (y < 1900 || y > 2100) {
            return -1;
        }
        // 公历传参最下限
        if (y === 1900 && m === 1 && d < 31) {
            return -1;
        }

        // 未传参获得当天
        let objDate;
        if (!y) {
            objDate = new Date();
        } else {
            objDate = new Date(y, parseInt(m) - 1, d);
        }
        let i, leap = 0, temp = 0;
        // 修正ymd参数
        y = objDate.getFullYear();
        m = objDate.getMonth() + 1;
        d = objDate.getDate();
        let offset = (Date.UTC(objDate.getFullYear(), objDate.getMonth(), objDate.getDate()) - Date.UTC(1900, 0, 31)) / 86400000;

        for (i = 1900; i < 2101 && offset > 0; i++) {
            temp = this.lYearDays(i);
            offset -= temp;
        }

        if (offset < 0) {
            offset += temp;
            i--;
        }

        // 是否今天
        const today = new Date();
        const isToday = today.getFullYear() === y && today.getMonth() + 1 === m && today.getDate() === d;

        // 星期几
        const nWeek = objDate.getDay();
        const cWeek = this.nStr1[nWeek];

        // 农历年
        const year = i;
        leap = this.leapMonth(i); // 闰哪个月
        let isLeap = false;

        // 效验闰月
        for (i = 1; i < 13 && offset > 0; i++) {
            // 闰月
            if (leap > 0 && i === (leap + 1) && isLeap === false) {
                --i;
                isLeap = true; temp = this.leapDays(year); // 计算农历闰月天数
            } else {
                temp = this.monthDays(year, i);// 计算农历普通月天数
            }
            // 解除闰月
            if (isLeap === true && i === (leap + 1)) { isLeap = false; }
            offset -= temp;
        }

        if (offset === 0 && leap > 0 && i === leap + 1) {
            if (isLeap) {
                isLeap = false;
            } else {
                isLeap = true; --i;
            }
        }

        if (offset < 0) {
            offset += temp; --i;
        }

        // 农历月
        const month = i;
        // 农历日
        const day = offset + 1;

        // 天干地支处理
        let sm = m - 1;
        const gzY = this.toGanZhiYear(year);

        // 当月的两个节气
        // 二十四节气速查表
        const gzM = this.toGanZhi((y - 1900) * 12 + m + 11);
        let firstNode = 0;
        let secondNode = 0;
        if (m === 2) {
            firstNode = 4; secondNode = 19;
        } else if (m === 3) {
            firstNode = 6; secondNode = 21;
        } else if (m === 4) {
            firstNode = 5; secondNode = 20;
        } else if (m === 5) {
            firstNode = 6; secondNode = 21;
        } else if (m === 6) {
            firstNode = 6; secondNode = 21;
        } else if (m === 7) {
            firstNode = 7; secondNode = 23;
        } else if (m === 8) {
            firstNode = 8; secondNode = 23;
        } else if (m === 9) {
            firstNode = 8; secondNode = 23;
        } else if (m === 10) {
            firstNode = 8; secondNode = 23;
        } else if (m === 11) {
            firstNode = 7; secondNode = 22;
        } else if (m === 12) {
            firstNode = 7; secondNode = 22;
        } else {
            firstNode = 6; secondNode = 20;
        }

        // 日柱 当月一日与 1900/1/1 相差天数
        const dayCyclical = Date.UTC(y, sm, 1, 0, 0, 0, 0) / 86400000 + 25567 + 10;
        const gzD = this.toGanZhi(dayCyclical + d - 1);

        // 该日期所属的星座
        const astro = this.toAstro(m, d);

        // 获取节日信息
        const solarFestivalKey = `${m}-${d}`;
        let lunarFestivalKey = `${month}-${day}`;
        
        // 特殊处理除夕：农历12月29或30日都可能是除夕
        if (month === 12 && (day === 29 || day === 30)) {
            // 检查是否是该年最后一天（除夕）
            const monthDays = this.monthDays(year, month);
            if (day === monthDays) {
                lunarFestivalKey = '12-30'; // 统一使用12-30作为除夕的key
            }
        }
        
        const solarFestival = this.solarFestival[solarFestivalKey] || null;
        const lunarFestival = this.lunarFestival[lunarFestivalKey] || null;

        return {
            'lYear': year,
            'lMonth': month,
            'lDay': day,
            'Animal': this.getAnimal(year),
            'IMonthCn': (isLeap ? "\u95f0" : '') + this.toChinaMonth(month),
            'IDayCn': this.toChinaDay(day),
            'cYear': y,
            'cMonth': m,
            'cDay': d,
            'gzYear': gzY,
            'gzMonth': gzM,
            'gzDay': gzD,
            'isToday': isToday,
            'isLeap': isLeap,
            'nWeek': nWeek,
            'ncWeek': "\u661f\u671f" + cWeek,
            'astro': astro,
            'festival': solarFestival,
            'lunarFestival': lunarFestival
        };
    }
};

// 导出为全局变量
if (typeof window !== 'undefined') {
    window.calendar = calendar;
}
