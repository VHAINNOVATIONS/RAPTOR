using System;
using System.Collections.Generic;
using System.Text;
using System.Collections;
using System.Reflection;
using System.Collections.Specialized;
using gov.va.medora.mdo;

namespace gov.va.medora.utils
{
    public class AssertionGenerator
    {
        public static void Print(object obj, int depth = Int32.MaxValue)
        {
            Print(obj, "result", depth);
        }

        public static void Print(object obj, string path, int depth = Int32.MaxValue)
        {
            if (null != obj)
            {
                Console.WriteLine("Assert.IsNotNull({0});", path);
            }

            AssertionGenerator ag = new AssertionGenerator(depth);
            ag.generateAssertions(obj, path);            
        }

        private int level;
        private int depth;
        public AssertionGenerator(int depth)
        {
            this.depth = depth;
            this.level = 0;
        }
        private Boolean IsTerminalObject(Object data)
        {
            Type t = data.GetType();
            return t.IsPrimitive || t.IsEnum || t.IsPointer || data is String || data is DateTime;
        }
        public void generateAssertions(object obj, string path)
        {
            string objPath = path;

            if (null == obj)
            {
                Console.WriteLine("Assert.IsNull({0});", path);
                return;
            }

            if (IsTerminalObject(obj))
            {
                if (obj.GetType() == typeof(bool) || obj.GetType() == typeof(Boolean))
                {
                    if ((bool)obj == true)
                    {
                        Console.WriteLine("Assert.IsTrue({0});", objPath);
                    }
                    else
                    {
                        Console.WriteLine("Assert.IsFalse({0});", objPath);
                    }
                }
                else if (obj.GetType() == typeof(char))
                {
                    Console.WriteLine("Assert.AreEqual('{0}', {1});", obj, objPath);
                }
                else if (obj.GetType() == typeof(string) || obj.GetType() == typeof(String))
                {
                    Console.WriteLine("Assert.AreEqual(\"{0}\", {1});", obj, objPath);
                }
                else if (obj is DateTime)
                {
                    Console.WriteLine("Assert.AreEqual({0}, DateTime.Parse(\"{1}\"));", objPath, obj);
                }
                else if (obj.GetType().IsEnum)
                {
                    Console.WriteLine("Assert.AreEqual({0}.{1}, {2});", obj.GetType(), obj, objPath);
                }
                else // value type
                {
                    Console.WriteLine("Assert.AreEqual({0}, {1});", obj, objPath);
                }

                return;
            } 
            else if (obj is Array || obj is IList)
            {
                IList list = (IList)obj;

                Console.WriteLine("Assert.AreEqual({0},{1}.{2});", list.Count, objPath, (obj is Array ? "Length" : "Count"));

                for (int i = 0; i < list.Count; i++)
                {
                    generateAssertions(list[i], String.Format("{0}[{1}]", objPath, i));
                }

                return;
            }
            else if (obj is StringDictionary)
            {
                doStringDictionary((StringDictionary)obj, objPath);
                return;
            }
            else if (obj.GetType().IsGenericType && obj.GetType().GetGenericTypeDefinition() == typeof(Dictionary<,>))
            {
                this.GetType().GetMethod("doDictionary")
                    .MakeGenericMethod(obj.GetType().GetGenericArguments())
                    .Invoke(this, new object[] { obj, objPath });
                return;
            }
            else if (obj.GetType() == typeof(Hashtable))
            {
                doHashtable((Hashtable)obj, objPath);
                return;
            }
            else if (obj.GetType() == typeof(DictionaryHashList))
            {
                doDictionaryHashList((DictionaryHashList)obj, objPath);
                return;
            }
            else if (obj.GetType() == typeof(IndexedHashtable))
            {
                doIndexedHashtable((IndexedHashtable)obj, objPath);
                return;
            }


            if (++level <= depth)
            {
                // only interested in publicly accessible properties
                PropertyInfo[] props = obj.GetType().GetProperties();
                MemberInfo[] members = obj.GetType().GetMembers();
                FieldInfo[] fields = obj.GetType().GetFields();
                foreach (PropertyInfo pi in props)
                {
                    objPath = path + "." + pi.Name;

                    var value = pi.GetValue(obj, null);
                    generateAssertions(value, objPath);
                }
                foreach (FieldInfo fi in fields)
                {
                    objPath = path + "." + fi.Name;

                    var value = fi.GetValue(obj);
                    generateAssertions(value, objPath);
                }
                System.Console.Write(members.Length);
                System.Console.Write(fields.Length);
            }
            else
            {
                Console.WriteLine("Assert.IsNotNull({0});", objPath);
            }

            level--;
        }
        public void doStringDictionary(StringDictionary dic, string objPath)
        {
            foreach (string key in dic.Keys)
            {
                Console.WriteLine("Assert.AreEqual(\"{0}\", {1}[\"{2}\"]);", dic[key], objPath, key);
            }
        }
        public void doDictionary<T, K>(Dictionary<T, K> dic, string objPath)
        {
            foreach (T key in dic.Keys)
            {
                if(IsTerminalObject(key)) 
                {
                    if (IsTerminalObject(dic[key]))
                    {
                        StringBuilder assertFormat = new StringBuilder("Assert.AreEqual(");

                        if (typeof(K) == typeof(String))
                            assertFormat.Append("\"{0}\"");
                        else
                            assertFormat.Append("{0}");

                        assertFormat.Append(", {1}[");

                        if (typeof(T) == typeof(String))
                            assertFormat.Append("\"{2}\"");
                        else
                            assertFormat.Append("{2}");

                        assertFormat.Append("]);");

                        Console.WriteLine(assertFormat.ToString(), dic[key], objPath, key);
                    }
                    else
                    {
                        string path = null;
                        if(typeof(T) == typeof(string)) 
                            path = objPath + "[\"" + key + "\"]";
                        else 
                            path = objPath + "[" + key + "]";

                        generateAssertions(dic[key], path);
                    }
                }
            }
        }

        public void doHashtable(Hashtable ht, string objPath)
        {
            Console.WriteLine("Assert.AreEqual({0},{1});", ht.Count, objPath+".Count");

            foreach (string key in ht.Keys)
            {
                Console.WriteLine("Assert.AreEqual(\"{0}\", {1}[\"{2}\"]);", ht[key], objPath, key);
            }
        }

        public void doDictionaryHashList(DictionaryHashList hl, string objPath)
        {
            Console.WriteLine("Assert.AreEqual({0},{1});", hl.Count, objPath + ".Count");

            foreach (string key in hl.Keys)
            {
                Console.WriteLine("Assert.AreEqual(\"{0}\", {1}[\"{2}\"]);", hl[key], objPath, key);
            }
        }

        public void doIndexedHashtable(IndexedHashtable hl, string objPath)
        {
            Console.WriteLine("Assert.AreEqual({0},{1});", hl.Count, objPath + ".Count");          
        }
        
    }
}
