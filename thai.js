/**
 * 泰国佛历（Buddhist Era）和西元阳历互转JavaScript库
 * 包含泰国传统节日和佛教节日识别功能
 */
const thaiCalendar = {
    // 泰文月份名称
    thaiMonthNames: [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ],

    // 泰文月份简称
    thaiMonthShort: [
        "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.",
        "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."
    ],

    // 泰文星期
    thaiDayNames: [
        "วันอาทิตย์", "วันจันทร์", "วันอังคาร", "วันพุธ", "วันพฤหัสบดี", "วันศุกร์", "วันเสาร์"
    ],

    // 泰文星期简称
    thaiDayShort: ["อา.", "จ.", "อ.", "พ.", "พฤ.", "ศ.", "ส."],

    // 泰文数字
    thaiNumbers: ["๐", "๑", "๒", "๓", "๔", "๕", "๖", "๗", "๘", "๙"],

    // 泰国固定节日（公历）
    thaiSolarFestivals: {
        '1-1': 'วันขึ้นปีใหม่', // 新年
        '2-14': 'วันวาเลนไทน์', // 情人节
        '4-6': 'วันจักรี', // 查卡里王朝纪念日
        '4-13': 'วันสงกรานต์', // 泼水节第一天
        '4-14': 'วันสงกรานต์', // 泼水节第二天
        '4-15': 'วันสงกรานต์', // 泼水节第三天
        '5-1': 'วันแรงงานแห่งชาติ', // 劳动节
        '5-4': 'วันฉัตรมงคล', // 加冕节
        '7-28': 'วันเฉลิมพระชนมพรรษา', // 国王生日
        '8-12': 'วันแม่แห่งชาติ', // 母亲节（前王后生日）
        '10-23': 'วันปิยมหาราช', // 朱拉隆功大帝纪念日
        '12-5': 'วันพ่อแห่งชาติ', // 父亲节（已故国王拉玛九世生日）
        '12-10': 'วันรัฐธรรมนูญ', // 宪法日
        '12-31': 'วันสิ้นปี' // 除夕
    },

    // 泰国佛教节日（佛历）- 这些通常根据月相计算，这里提供2024-2026年的近似日期
    thaiBuddhistFestivals: {
        // 2024年
        '2024-1-25': 'วันมาฆบูชา', // 万佛节
        '2024-3-25': 'วันวิสาขบูชา', // 卫塞节
        '2024-5-22': 'วันอาสาฬหบูชา', // 阿萨哈布达节
        '2024-5-23': 'วันเข้าพรรษา', // 入夏安居节
        '2024-10-20': 'วันออกพรรษา', // 出夏安居节
        '2024-11-15': 'วันลอยกระทง', // 水灯节
        
        // 2025年
        '2025-2-12': 'วันมาฆบูชา', // 万佛节
        '2025-4-13': 'วันวิสาขบูชา', // 卫塞节
        '2025-6-11': 'วันอาสาฬหบูชา', // 阿萨哈布达节
        '2025-6-12': 'วันเข้าพรรษา', // 入夏安居节
        '2025-11-8': 'วันออกพรรษา', // 出夏安居节
        '2025-11-5': 'วันลอยกระทง', // 水灯节
        
        // 2026年
        '2026-3-4': 'วันมาฆบูชา', // 万佛节
        '2026-5-2': 'วันวิสาขบูชา', // 卫塞节
        '2026-7-31': 'วันอาสาฬหบูชา', // 阿萨哈布达节
        '2026-8-1': 'วันเข้าพรรษา', // 入夏安居节
        '2026-10-28': 'วันออกพรรษา', // 出夏安居节
        '2026-11-24': 'วันลอยกระทง' // 水灯节
    },

    /**
     * 将阿拉伯数字转换为泰文数字
     */
    toThaiNumber: function(num) {
        return num.toString().split('').map(digit => this.thaiNumbers[parseInt(digit)]).join('');
    },

    /**
     * 将西元年份转换为佛历年份
     * 佛历 = 西元年 + 543
     */
    toBuddhistYear: function(gregorianYear) {
        return gregorianYear + 543;
    },

    /**
     * 将佛历年份转换为西元年份
     */
    toGregorianYear: function(buddhistYear) {
        return buddhistYear - 543;
    },

    /**
     * 获取泰文月份名称
     */
    getThaiMonthName: function(month, isShort = false) {
        if (month < 1 || month > 12) return '';
        return isShort ? this.thaiMonthShort[month - 1] : this.thaiMonthNames[month - 1];
    },

    /**
     * 获取泰文星期名称
     */
    getThaiDayName: function(dayOfWeek, isShort = false) {
        if (dayOfWeek < 0 || dayOfWeek > 6) return '';
        return isShort ? this.thaiDayShort[dayOfWeek] : this.thaiDayNames[dayOfWeek];
    },

    /**
     * 格式化泰国日期
     * @param {number} year - 西元年
     * @param {number} month - 月份 (1-12)
     * @param {number} day - 日期
     * @param {boolean} useBuddhistEra - 是否使用佛历
     * @param {boolean} useThaiNumbers - 是否使用泰文数字
     * @param {boolean} isShort - 是否使用简称
     */
    formatThaiDate: function(year, month, day, useBuddhistEra = true, useThaiNumbers = false, isShort = false) {
        const date = new Date(year, month - 1, day);
        const dayOfWeek = date.getDay();
        
        let displayYear = useBuddhistEra ? this.toBuddhistYear(year) : year;
        let displayMonth = month;
        let displayDay = day;
        
        if (useThaiNumbers) {
            displayYear = this.toThaiNumber(displayYear);
            displayMonth = this.toThaiNumber(displayMonth);
            displayDay = this.toThaiNumber(displayDay);
        }
        
        const dayName = this.getThaiDayName(dayOfWeek, isShort);
        const monthName = this.getThaiMonthName(month, isShort);
        
        return {
            dayName: dayName,
            day: displayDay,
            month: displayMonth,
            monthName: monthName,
            year: displayYear,
            formatted: `${dayName}ที่ ${displayDay} ${monthName} ${displayYear}`,
            simple: `${displayDay} ${monthName} ${displayYear}`
        };
    },

    /**
     * 获取指定日期的节日信息
     */
    getFestival: function(year, month, day) {
        const dateKey = `${month}-${day}`;
        const fullDateKey = `${year}-${month}-${day}`;
        
        // 检查固定节日
        const solarFestival = this.thaiSolarFestivals[dateKey];
        
        // 检查佛教节日
        const buddhistFestival = this.thaiBuddhistFestivals[fullDateKey];
        
        return {
            solar: solarFestival || null,
            buddhist: buddhistFestival || null,
            main: buddhistFestival || solarFestival || null
        };
    },

    /**
     * 转换公历到泰国日期格式并获取完整信息
     */
    gregorianToThai: function(year, month, day) {
        if (!year || !month || !day) {
            const today = new Date();
            year = today.getFullYear();
            month = today.getMonth() + 1;
            day = today.getDate();
        }

        const date = new Date(year, month - 1, day);
        const dayOfWeek = date.getDay();
        
        // 格式化泰国日期
        const thaiDate = this.formatThaiDate(year, month, day, true, false, false);
        const thaiDateShort = this.formatThaiDate(year, month, day, true, false, true);
        const thaiDateNumbers = this.formatThaiDate(year, month, day, true, true, false);
        
        // 获取节日信息
        const festivals = this.getFestival(year, month, day);
        
        // 检查是否是今天
        const today = new Date();
        const isToday = today.getFullYear() === year && 
                       today.getMonth() + 1 === month && 
                       today.getDate() === day;
        
        return {
            // 西元日期
            gregorianYear: year,
            gregorianMonth: month,
            gregorianDay: day,
            
            // 佛历年份
            buddhistYear: this.toBuddhistYear(year),
            
            // 星期
            dayOfWeek: dayOfWeek,
            dayName: thaiDate.dayName,
            dayNameShort: this.getThaiDayName(dayOfWeek, true),
            
            // 月份信息
            monthName: thaiDate.monthName,
            monthNameShort: this.getThaiMonthName(month, true),
            
            // 格式化日期
            formatted: thaiDate.formatted,
            simple: thaiDate.simple,
            shortFormat: thaiDateShort.simple,
            thaiNumbers: thaiDateNumbers.simple,
            
            // 节日信息
            festival: festivals.main,
            solarFestival: festivals.solar,
            buddhistFestival: festivals.buddhist,
            
            // 其他信息
            isToday: isToday
        };
    },

    /**
     * 获取本月的所有节日
     */
    getMonthFestivals: function(year, month) {
        const festivals = [];
        const daysInMonth = new Date(year, month, 0).getDate();
        
        for (let day = 1; day <= daysInMonth; day++) {
            const festival = this.getFestival(year, month, day);
            if (festival.main) {
                festivals.push({
                    date: `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`,
                    day: day,
                    festival: festival.main,
                    type: festival.buddhist ? 'buddhist' : 'solar'
                });
            }
        }
        
        return festivals;
    },

    /**
     * 检查指定年份是否为闰年
     */
    isLeapYear: function(year) {
        return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
    },

    /**
     * 获取指定月份的天数
     */
    getDaysInMonth: function(year, month) {
        const daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        if (month === 2 && this.isLeapYear(year)) {
            return 29;
        }
        return daysInMonth[month - 1];
    }
};

// 导出为全局变量
if (typeof window !== 'undefined') {
    window.thaiCalendar = thaiCalendar;
}
