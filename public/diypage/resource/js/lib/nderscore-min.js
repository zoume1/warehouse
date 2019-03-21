(function() {
	function n(n) {
		function r(r, t, e, u, i, o) {
			for(; i >= 0 && o > i; i += n) {
				var a = u ? u[i] : i;
				e = t(e, r[a], a, r)
			}
			return e
		}
		return function(t, e, u, i) {
			e = b(e, i, 4);
			var o = !k(t) && m.keys(t),
				a = (o || t).length,
				c = n > 0 ? 0 : a - 1;
			return arguments.length < 3 && (u = t[o ? o[c] : c], c += n), r(t, e, u, o, c, a)
		}
	}

	function r(n) {
		return function(r, t, e) {
			t = x(t, e);
			for(var u = O(r), i = n > 0 ? 0 : u - 1; i >= 0 && u > i; i += n)
				if(t(r[i], i, r)) return i;
			return -1
		}
	}

	function t(n, r, t) {
		return function(e, u, i) {
			var o = 0,
				a = O(e);
			if("number" == typeof i) n > 0 ? o = i >= 0 ? i : Math.max(i + a, o) : a = i >= 0 ? Math.min(i + 1, a) : i + a + 1;
			else if(t && i && a) return i = t(e, u), e[i] === u ? i : -1;
			if(u !== u) return(i = r(l.call(e, o, a), m.isNaN)) >= 0 ? i + o : -1;
			for(i = n > 0 ? o : a - 1; i >= 0 && a > i; i += n)
				if(e[i] === u) return i;
			return -1
		}
	}

	function e(n, r) {
		var t = I.length,
			e = n.constructor,
			u = m.isFunction(e) && e.prototype || a,
			i = "constructor";
		for(m.has(n, i) && !m.contains(r, i) && r.push(i); t--;)(i = I[t]) in n && n[i] !== u[i] && !m.contains(r, i) && r.push(i)
	}
	var u = this,
		i = u._,
		o = Array.prototype,
		a = Object.prototype,
		c = Function.prototype,
		f = o.push,
		l = o.slice,
		s = a.toString,
		p = a.hasOwnProperty,
		h = Array.isArray,
		v = Object.keys,
		y = c.bind,
		d = Object.create,
		g = function() {},
		m = function(n) {
			return n instanceof m ? n : this instanceof m ? void(this._wrapped = n) : new m(n)
		};
	"undefined" != typeof exports ? ("undefined" != typeof module && module.exports && (exports = module.exports = m), exports._ = m) : u._ = m, m.VERSION = "1.8.3";
	var b = function(n, r, t) {
			if(void 0 === r) return n;
			switch(null == t ? 3 : t) {
				case 1:
					return function(t) {
						return n.call(r, t)
					};
				case 2:
					return function(t, e) {
						return n.call(r, t, e)
					};
				case 3:
					return function(t, e, u) {
						return n.call(r, t, e, u)
					};
				case 4:
					return function(t, e, u, i) {
						return n.call(r, t, e, u, i)
					}
			}
			return function() {
				return n.apply(r, arguments)
			}
		},
		x = function(n, r, t) {
			return null == n ? m.identity : m.isFunction(n) ? b(n, r, t) : m.isObject(n) ? m.matcher(n) : m.property(n)
		};
	m.iteratee = function(n, r) {
		return x(n, r, 1 / 0)
	};
	var _ = function(n, r) {
			return function(t) {
				var e = arguments.length;
				if(2 > e || null == t) return t;
				for(var u = 1; e > u; u++)
					for(var i = arguments[u], o = n(i), a = o.length, c = 0; a > c; c++) {
						var f = o[c];
						r && void 0 !== t[f] || (t[f] = i[f])
					}
				return t
			}
		},
		j = function(n) {
			if(!m.isObject(n)) return {};
			if(d) return d(n);
			g.prototype = n;
			var r = new g;
			return g.prototype = null, r
		},
		w = function(n) {
			return function(r) {
				return null == r ? void 0 : r[n]
			}
		},
		A = Math.pow(2, 53) - 1,
		O = w("length"),
		k = function(n) {
			var r = O(n);
			return "number" == typeof r && r >= 0 && A >= r
		};
	m.each = m.forEach = function(n, r, t) {
		r = b(r, t);
		var e, u;
		if(k(n))
			for(e = 0, u = n.length; u > e; e++) r(n[e], e, n);
		else {
			var i = m.keys(n);
			for(e = 0, u = i.length; u > e; e++) r(n[i[e]], i[e], n)
		}
		return n
	}, m.map = m.collect = function(n, r, t) {
		r = x(r, t);
		for(var e = !k(n) && m.keys(n), u = (e || n).length, i = Array(u), o = 0; u > o; o++) {
			var a = e ? e[o] : o;
			i[o] = r(n[a], a, n)
		}
		return i
	}, m.reduce = m.foldl = m.inject = n(1), m.reduceRight = m.foldr = n(-1), m.find = m.detect = function(n, r, t) {
		var e;
		return void 0 !== (e = k(n) ? m.findIndex(n, r, t) : m.findKey(n, r, t)) && -1 !== e ? n[e] : void 0
	}, m.filter = m.select = function(n, r, t) {
		var e = [];
		return r = x(r, t), m.each(n, function(n, t, u) {
			r(n, t, u) && e.push(n)
		}), e
	}, m.reject = function(n, r, t) {
		return m.filter(n, m.negate(x(r)), t)
	}, m.every = m.all = function(n, r, t) {
		r = x(r, t);
		for(var e = !k(n) && m.keys(n), u = (e || n).length, i = 0; u > i; i++) {
			var o = e ? e[i] : i;
			if(!r(n[o], o, n)) return !1
		}
		return !0
	}, m.some = m.any = function(n, r, t) {
		r = x(r, t);
		for(var e = !k(n) && m.keys(n), u = (e || n).length, i = 0; u > i; i++) {
			var o = e ? e[i] : i;
			if(r(n[o], o, n)) return !0
		}
		return !1
	}, m.contains = m.includes = m.include = function(n, r, t, e) {
		return k(n) || (n = m.values(n)), ("number" != typeof t || e) && (t = 0), m.indexOf(n, r, t) >= 0
	}, m.invoke = function(n, r) {
		var t = l.call(arguments, 2),
			e = m.isFunction(r);
		return m.map(n, function(n) {
			var u = e ? r : n[r];
			return null == u ? u : u.apply(n, t)
		})
	}, m.pluck = function(n, r) {
		return m.map(n, m.property(r))
	}, m.where = function(n, r) {
		return m.filter(n, m.matcher(r))
	}, m.findWhere = function(n, r) {
		return m.find(n, m.matcher(r))
	}, m.max = function(n, r, t) {
		var e, u, i = -1 / 0,
			o = -1 / 0;
		if(null == r && null != n)
			for(var a = 0, c = (n = k(n) ? n : m.values(n)).length; c > a; a++)(e = n[a]) > i && (i = e);
		else r = x(r, t), m.each(n, function(n, t, e) {
			((u = r(n, t, e)) > o || u === -1 / 0 && i === -1 / 0) && (i = n, o = u)
		});
		return i
	}, m.min = function(n, r, t) {
		var e, u, i = 1 / 0,
			o = 1 / 0;
		if(null == r && null != n)
			for(var a = 0, c = (n = k(n) ? n : m.values(n)).length; c > a; a++) e = n[a], i > e && (i = e);
		else r = x(r, t), m.each(n, function(n, t, e) {
			u = r(n, t, e), (o > u || 1 / 0 === u && 1 / 0 === i) && (i = n, o = u)
		});
		return i
	}, m.shuffle = function(n) {
		for(var r, t = k(n) ? n : m.values(n), e = t.length, u = Array(e), i = 0; e > i; i++)(r = m.random(0, i)) !== i && (u[i] = u[r]), u[r] = t[i];
		return u
	}, m.sample = function(n, r, t) {
		return null == r || t ? (k(n) || (n = m.values(n)), n[m.random(n.length - 1)]) : m.shuffle(n).slice(0, Math.max(0, r))
	}, m.sortBy = function(n, r, t) {
		return r = x(r, t), m.pluck(m.map(n, function(n, t, e) {
			return {
				value: n,
				index: t,
				criteria: r(n, t, e)
			}
		}).sort(function(n, r) {
			var t = n.criteria,
				e = r.criteria;
			if(t !== e) {
				if(t > e || void 0 === t) return 1;
				if(e > t || void 0 === e) return -1
			}
			return n.index - r.index
		}), "value")
	};
	var F = function(n) {
		return function(r, t, e) {
			var u = {};
			return t = x(t, e), m.each(r, function(e, i) {
				var o = t(e, i, r);
				n(u, e, o)
			}), u
		}
	};
	m.groupBy = F(function(n, r, t) {
		m.has(n, t) ? n[t].push(r) : n[t] = [r]
	}), m.indexBy = F(function(n, r, t) {
		n[t] = r
	}), m.countBy = F(function(n, r, t) {
		m.has(n, t) ? n[t]++ : n[t] = 1
	}), m.toArray = function(n) {
		return n ? m.isArray(n) ? l.call(n) : k(n) ? m.map(n, m.identity) : m.values(n) : []
	}, m.size = function(n) {
		return null == n ? 0 : k(n) ? n.length : m.keys(n).length
	}, m.partition = function(n, r, t) {
		r = x(r, t);
		var e = [],
			u = [];
		return m.each(n, function(n, t, i) {
			(r(n, t, i) ? e : u).push(n)
		}), [e, u]
	}, m.first = m.head = m.take = function(n, r, t) {
		return null == n ? void 0 : null == r || t ? n[0] : m.initial(n, n.length - r)
	}, m.initial = function(n, r, t) {
		return l.call(n, 0, Math.max(0, n.length - (null == r || t ? 1 : r)))
	}, m.last = function(n, r, t) {
		return null == n ? void 0 : null == r || t ? n[n.length - 1] : m.rest(n, Math.max(0, n.length - r))
	}, m.rest = m.tail = m.drop = function(n, r, t) {
		return l.call(n, null == r || t ? 1 : r)
	}, m.compact = function(n) {
		return m.filter(n, m.identity)
	};
	var S = function(n, r, t, e) {
		for(var u = [], i = 0, o = e || 0, a = O(n); a > o; o++) {
			var c = n[o];
			if(k(c) && (m.isArray(c) || m.isArguments(c))) {
				r || (c = S(c, r, t));
				var f = 0,
					l = c.length;
				for(u.length += l; l > f;) u[i++] = c[f++]
			} else t || (u[i++] = c)
		}
		return u
	};
	m.flatten = function(n, r) {
		return S(n, r, !1)
	}, m.without = function(n) {
		return m.difference(n, l.call(arguments, 1))
	}, m.uniq = m.unique = function(n, r, t, e) {
		m.isBoolean(r) || (e = t, t = r, r = !1), null != t && (t = x(t, e));
		for(var u = [], i = [], o = 0, a = O(n); a > o; o++) {
			var c = n[o],
				f = t ? t(c, o, n) : c;
			r ? (o && i === f || u.push(c), i = f) : t ? m.contains(i, f) || (i.push(f), u.push(c)) : m.contains(u, c) || u.push(c)
		}
		return u
	}, m.union = function() {
		return m.uniq(S(arguments, !0, !0))
	}, m.intersection = function(n) {
		for(var r = [], t = arguments.length, e = 0, u = O(n); u > e; e++) {
			var i = n[e];
			if(!m.contains(r, i)) {
				for(var o = 1; t > o && m.contains(arguments[o], i); o++);
				o === t && r.push(i)
			}
		}
		return r
	}, m.difference = function(n) {
		var r = S(arguments, !0, !0, 1);
		return m.filter(n, function(n) {
			return !m.contains(r, n)
		})
	}, m.zip = function() {
		return m.unzip(arguments)
	}, m.unzip = function(n) {
		for(var r = n && m.max(n, O).length || 0, t = Array(r), e = 0; r > e; e++) t[e] = m.pluck(n, e);
		return t
	}, m.object = function(n, r) {
		for(var t = {}, e = 0, u = O(n); u > e; e++) r ? t[n[e]] = r[e] : t[n[e][0]] = n[e][1];
		return t
	}, m.findIndex = r(1), m.findLastIndex = r(-1), m.sortedIndex = function(n, r, t, e) {
		for(var u = (t = x(t, e, 1))(r), i = 0, o = O(n); o > i;) {
			var a = Math.floor((i + o) / 2);
			t(n[a]) < u ? i = a + 1 : o = a
		}
		return i
	}, m.indexOf = t(1, m.findIndex, m.sortedIndex), m.lastIndexOf = t(-1, m.findLastIndex), m.range = function(n, r, t) {
		null == r && (r = n || 0, n = 0), t = t || 1;
		for(var e = Math.max(Math.ceil((r - n) / t), 0), u = Array(e), i = 0; e > i; i++, n += t) u[i] = n;
		return u
	};
	var E = function(n, r, t, e, u) {
		if(!(e instanceof r)) return n.apply(t, u);
		var i = j(n.prototype),
			o = n.apply(i, u);
		return m.isObject(o) ? o : i
	};
	m.bind = function(n, r) {
		if(y && n.bind === y) return y.apply(n, l.call(arguments, 1));
		if(!m.isFunction(n)) throw new TypeError("Bind must be called on a function");
		var t = l.call(arguments, 2),
			e = function() {
				return E(n, e, r, this, t.concat(l.call(arguments)))
			};
		return e
	}, m.partial = function(n) {
		var r = l.call(arguments, 1),
			t = function() {
				for(var e = 0, u = r.length, i = Array(u), o = 0; u > o; o++) i[o] = r[o] === m ? arguments[e++] : r[o];
				for(; e < arguments.length;) i.push(arguments[e++]);
				return E(n, t, this, this, i)
			};
		return t
	}, m.bindAll = function(n) {
		var r, t, e = arguments.length;
		if(1 >= e) throw new Error("bindAll must be passed function names");
		for(r = 1; e > r; r++) t = arguments[r], n[t] = m.bind(n[t], n);
		return n
	}, m.memoize = function(n, r) {
		var t = function(e) {
			var u = t.cache,
				i = "" + (r ? r.apply(this, arguments) : e);
			return m.has(u, i) || (u[i] = n.apply(this, arguments)), u[i]
		};
		return t.cache = {}, t
	}, m.delay = function(n, r) {
		var t = l.call(arguments, 2);
		return setTimeout(function() {
			return n.apply(null, t)
		}, r)
	}, m.defer = m.partial(m.delay, m, 1), m.throttle = function(n, r, t) {
		var e, u, i, o = null,
			a = 0;
		t || (t = {});
		var c = function() {
			a = !1 === t.leading ? 0 : m.now(), o = null, i = n.apply(e, u), o || (e = u = null)
		};
		return function() {
			var f = m.now();
			a || !1 !== t.leading || (a = f);
			var l = r - (f - a);
			return e = this, u = arguments, 0 >= l || l > r ? (o && (clearTimeout(o), o = null), a = f, i = n.apply(e, u), o || (e = u = null)) : o || !1 === t.trailing || (o = setTimeout(c, l)), i
		}
	}, m.debounce = function(n, r, t) {
		var e, u, i, o, a, c = function() {
			var f = m.now() - o;
			r > f && f >= 0 ? e = setTimeout(c, r - f) : (e = null, t || (a = n.apply(i, u), e || (i = u = null)))
		};
		return function() {
			i = this, u = arguments, o = m.now();
			var f = t && !e;
			return e || (e = setTimeout(c, r)), f && (a = n.apply(i, u), i = u = null), a
		}
	}, m.wrap = function(n, r) {
		return m.partial(r, n)
	}, m.negate = function(n) {
		return function() {
			return !n.apply(this, arguments)
		}
	}, m.compose = function() {
		var n = arguments,
			r = n.length - 1;
		return function() {
			for(var t = r, e = n[r].apply(this, arguments); t--;) e = n[t].call(this, e);
			return e
		}
	}, m.after = function(n, r) {
		return function() {
			return --n < 1 ? r.apply(this, arguments) : void 0
		}
	}, m.before = function(n, r) {
		var t;
		return function() {
			return --n > 0 && (t = r.apply(this, arguments)), 1 >= n && (r = null), t
		}
	}, m.once = m.partial(m.before, 2);
	var M = !{
			toString: null
		}.propertyIsEnumerable("toString"),
		I = ["valueOf", "isPrototypeOf", "toString", "propertyIsEnumerable", "hasOwnProperty", "toLocaleString"];
	m.keys = function(n) {
		if(!m.isObject(n)) return [];
		if(v) return v(n);
		var r = [];
		for(var t in n) m.has(n, t) && r.push(t);
		return M && e(n, r), r
	}, m.allKeys = function(n) {
		if(!m.isObject(n)) return [];
		var r = [];
		for(var t in n) r.push(t);
		return M && e(n, r), r
	}, m.values = function(n) {
		for(var r = m.keys(n), t = r.length, e = Array(t), u = 0; t > u; u++) e[u] = n[r[u]];
		return e
	}, m.mapObject = function(n, r, t) {
		r = x(r, t);
		for(var e, u = m.keys(n), i = u.length, o = {}, a = 0; i > a; a++) e = u[a], o[e] = r(n[e], e, n);
		return o
	}, m.pairs = function(n) {
		for(var r = m.keys(n), t = r.length, e = Array(t), u = 0; t > u; u++) e[u] = [r[u], n[r[u]]];
		return e
	}, m.invert = function(n) {
		for(var r = {}, t = m.keys(n), e = 0, u = t.length; u > e; e++) r[n[t[e]]] = t[e];
		return r
	}, m.functions = m.methods = function(n) {
		var r = [];
		for(var t in n) m.isFunction(n[t]) && r.push(t);
		return r.sort()
	}, m.extend = _(m.allKeys), m.extendOwn = m.assign = _(m.keys), m.findKey = function(n, r, t) {
		r = x(r, t);
		for(var e, u = m.keys(n), i = 0, o = u.length; o > i; i++)
			if(e = u[i], r(n[e], e, n)) return e
	}, m.pick = function(n, r, t) {
		var e, u, i = {},
			o = n;
		if(null == o) return i;
		m.isFunction(r) ? (u = m.allKeys(o), e = b(r, t)) : (u = S(arguments, !1, !1, 1), e = function(n, r, t) {
			return r in t
		}, o = Object(o));
		for(var a = 0, c = u.length; c > a; a++) {
			var f = u[a],
				l = o[f];
			e(l, f, o) && (i[f] = l)
		}
		return i
	}, m.omit = function(n, r, t) {
		if(m.isFunction(r)) r = m.negate(r);
		else {
			var e = m.map(S(arguments, !1, !1, 1), String);
			r = function(n, r) {
				return !m.contains(e, r)
			}
		}
		return m.pick(n, r, t)
	}, m.defaults = _(m.allKeys, !0), m.create = function(n, r) {
		var t = j(n);
		return r && m.extendOwn(t, r), t
	}, m.clone = function(n) {
		return m.isObject(n) ? m.isArray(n) ? n.slice() : m.extend({}, n) : n
	}, m.tap = function(n, r) {
		return r(n), n
	}, m.isMatch = function(n, r) {
		var t = m.keys(r),
			e = t.length;
		if(null == n) return !e;
		for(var u = Object(n), i = 0; e > i; i++) {
			var o = t[i];
			if(r[o] !== u[o] || !(o in u)) return !1
		}
		return !0
	};
	var N = function(n, r, t, e) {
		if(n === r) return 0 !== n || 1 / n == 1 / r;
		if(null == n || null == r) return n === r;
		n instanceof m && (n = n._wrapped), r instanceof m && (r = r._wrapped);
		var u = s.call(n);
		if(u !== s.call(r)) return !1;
		switch(u) {
			case "[object RegExp]":
			case "[object String]":
				return "" + n == "" + r;
			case "[object Number]":
				return +n != +n ? +r != +r : 0 == +n ? 1 / +n == 1 / r : +n == +r;
			case "[object Date]":
			case "[object Boolean]":
				return +n == +r
		}
		var i = "[object Array]" === u;
		if(!i) {
			if("object" != typeof n || "object" != typeof r) return !1;
			var o = n.constructor,
				a = r.constructor;
			if(o !== a && !(m.isFunction(o) && o instanceof o && m.isFunction(a) && a instanceof a) && "constructor" in n && "constructor" in r) return !1
		}
		t = t || [], e = e || [];
		for(var c = t.length; c--;)
			if(t[c] === n) return e[c] === r;
		if(t.push(n), e.push(r), i) {
			if((c = n.length) !== r.length) return !1;
			for(; c--;)
				if(!N(n[c], r[c], t, e)) return !1
		} else {
			var f, l = m.keys(n);
			if(c = l.length, m.keys(r).length !== c) return !1;
			for(; c--;)
				if(f = l[c], !m.has(r, f) || !N(n[f], r[f], t, e)) return !1
		}
		return t.pop(), e.pop(), !0
	};
	m.isEqual = function(n, r) {
		return N(n, r)
	}, m.isEmpty = function(n) {
		return null == n || (k(n) && (m.isArray(n) || m.isString(n) || m.isArguments(n)) ? 0 === n.length : 0 === m.keys(n).length)
	}, m.isElement = function(n) {
		return !(!n || 1 !== n.nodeType)
	}, m.isArray = h || function(n) {
		return "[object Array]" === s.call(n)
	}, m.isObject = function(n) {
		var r = typeof n;
		return "function" === r || "object" === r && !!n
	}, m.each(["Arguments", "Function", "String", "Number", "Date", "RegExp", "Error"], function(n) {
		m["is" + n] = function(r) {
			return s.call(r) === "[object " + n + "]"
		}
	}), m.isArguments(arguments) || (m.isArguments = function(n) {
		return m.has(n, "callee")
	}), "function" != typeof /./ && "object" != typeof Int8Array && (m.isFunction = function(n) {
		return "function" == typeof n || !1
	}), m.isFinite = function(n) {
		return isFinite(n) && !isNaN(parseFloat(n))
	}, m.isNaN = function(n) {
		return m.isNumber(n) && n !== +n
	}, m.isBoolean = function(n) {
		return !0 === n || !1 === n || "[object Boolean]" === s.call(n)
	}, m.isNull = function(n) {
		return null === n
	}, m.isUndefined = function(n) {
		return void 0 === n
	}, m.has = function(n, r) {
		return null != n && p.call(n, r)
	}, m.noConflict = function() {
		return u._ = i, this
	}, m.identity = function(n) {
		return n
	}, m.constant = function(n) {
		return function() {
			return n
		}
	}, m.noop = function() {}, m.property = w, m.propertyOf = function(n) {
		return null == n ? function() {} : function(r) {
			return n[r]
		}
	}, m.matcher = m.matches = function(n) {
		return n = m.extendOwn({}, n),
			function(r) {
				return m.isMatch(r, n)
			}
	}, m.times = function(n, r, t) {
		var e = Array(Math.max(0, n));
		r = b(r, t, 1);
		for(var u = 0; n > u; u++) e[u] = r(u);
		return e
	}, m.random = function(n, r) {
		return null == r && (r = n, n = 0), n + Math.floor(Math.random() * (r - n + 1))
	}, m.now = Date.now || function() {
		return(new Date).getTime()
	};
	var B = {
			"&": "&amp;",
			"<": "&lt;",
			">": "&gt;",
			'"': "&quot;",
			"'": "&#x27;",
			"`": "&#x60;"
		},
		T = m.invert(B),
		R = function(n) {
			var r = function(r) {
					return n[r]
				},
				t = "(?:" + m.keys(n).join("|") + ")",
				e = RegExp(t),
				u = RegExp(t, "g");
			return function(n) {
				return n = null == n ? "" : "" + n, e.test(n) ? n.replace(u, r) : n
			}
		};
	m.escape = R(B), m.unescape = R(T), m.result = function(n, r, t) {
		var e = null == n ? void 0 : n[r];
		return void 0 === e && (e = t), m.isFunction(e) ? e.call(n) : e
	};
	var q = 0;
	m.uniqueId = function(n) {
		var r = ++q + "";
		return n ? n + r : r
	}, m.templateSettings = {
		evaluate: /<%([\s\S]+?)%>/g,
		interpolate: /<%=([\s\S]+?)%>/g,
		escape: /<%-([\s\S]+?)%>/g
	};
	var K = /(.)^/,
		z = {
			"'": "'",
			"\\": "\\",
			"\r": "r",
			"\n": "n",
			"\u2028": "u2028",
			"\u2029": "u2029"
		},
		D = /\\|'|\r|\n|\u2028|\u2029/g,
		L = function(n) {
			return "\\" + z[n]
		};
	m.template = function(n, r, t) {
		!r && t && (r = t), r = m.defaults({}, r, m.templateSettings);
		var e = RegExp([(r.escape || K).source, (r.interpolate || K).source, (r.evaluate || K).source].join("|") + "|$", "g"),
			u = 0,
			i = "__p+='";
		n.replace(e, function(r, t, e, o, a) {
			return i += n.slice(u, a).replace(D, L), u = a + r.length, t ? i += "'+\n((__t=(" + t + "))==null?'':_.escape(__t))+\n'" : e ? i += "'+\n((__t=(" + e + "))==null?'':__t)+\n'" : o && (i += "';\n" + o + "\n__p+='"), r
		}), i += "';\n", r.variable || (i = "with(obj||{}){\n" + i + "}\n"), i = "var __t,__p='',__j=Array.prototype.join,print=function(){__p+=__j.call(arguments,'');};\n" + i + "return __p;\n";
		try {
			var o = new Function(r.variable || "obj", "_", i)
		} catch(n) {
			throw n.source = i, n
		}
		var a = function(n) {
				return o.call(this, n, m)
			},
			c = r.variable || "obj";
		return a.source = "function(" + c + "){\n" + i + "}", a
	}, m.chain = function(n) {
		var r = m(n);
		return r._chain = !0, r
	};
	var P = function(n, r) {
		return n._chain ? m(r).chain() : r
	};
	m.mixin = function(n) {
		m.each(m.functions(n), function(r) {
			var t = m[r] = n[r];
			m.prototype[r] = function() {
				var n = [this._wrapped];
				return f.apply(n, arguments), P(this, t.apply(m, n))
			}
		})
	}, m.mixin(m), m.each(["pop", "push", "reverse", "shift", "sort", "splice", "unshift"], function(n) {
		var r = o[n];
		m.prototype[n] = function() {
			var t = this._wrapped;
			return r.apply(t, arguments), "shift" !== n && "splice" !== n || 0 !== t.length || delete t[0], P(this, t)
		}
	}), m.each(["concat", "join", "slice"], function(n) {
		var r = o[n];
		m.prototype[n] = function() {
			return P(this, r.apply(this._wrapped, arguments))
		}
	}), m.prototype.value = function() {
		return this._wrapped
	}, m.prototype.valueOf = m.prototype.toJSON = m.prototype.value, m.prototype.toString = function() {
		return "" + this._wrapped
	}, "function" == typeof define && define.amd && define("underscore", [], function() {
		return m
	})
}).call(this);