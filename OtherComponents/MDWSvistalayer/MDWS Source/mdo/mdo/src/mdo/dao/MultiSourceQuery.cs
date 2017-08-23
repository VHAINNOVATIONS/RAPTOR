using System;
using System.Collections;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using System.Threading;

namespace gov.va.medora.mdo.dao
{
    public class MultiSourceQuery
    {
        IndexedHashtable cxnTable = new IndexedHashtable();
    	
	    public MultiSourceQuery(DataSource[] sources)
	    {
		    addConnections(sources);
	    }

        public MultiSourceQuery(IndexedHashtable cxnTable)
        {
            this.cxnTable = cxnTable;
        }

        public IndexedHashtable ConnectionTable
        {
            get { return cxnTable; }
        }

        public void addConnections(DataSource[] sources)
        {
            for (int i = 0; i < sources.Length; i++)
            {
                if (cxnTable.ContainsKey(sources[i].SiteId.Key))
                {
                    continue;
                }
                int protocol = DaoFactory.getConstant(sources[i].Protocol);
                DaoFactory daoFactory = DaoFactory.getDaoFactory(protocol);
                cxnTable.Add(sources[i].SiteId.Key, daoFactory.getConnection(sources[i]));
            }
        }

        public void addConnections(MultiSourceQuery msq2)
        {
            for (int i = 0; i < msq2.cxnTable.Count; i++)
            {
                if (!this.cxnTable.ContainsKey((string)msq2.cxnTable.GetKey(i)))
                {
                    this.cxnTable.Add(msq2.cxnTable.GetKey(i), msq2.cxnTable.GetValue(i));
                }
            }
        }

        public int Count
        {
            get { return cxnTable.Count; }
        }

        public bool siteIsConnected(String sitecode)
        {
            return cxnTable.ContainsKey(sitecode);
        }

        public Connection getConnection(String sitecode)
        {
            if (!siteIsConnected(sitecode))
            {
                throw new Exception("Site is not connected");
            }
            return (Connection)cxnTable.GetValue(sitecode);
        }

        public Connection getConnection(int idx)
        {
            if (cxnTable.Count < (idx + 1))
            {
                throw new Exception("No such connection");
            }
            return (Connection)cxnTable.GetValue(idx);
        }

        public String getDefaultSiteId()
        {
            return (String)cxnTable.GetKey(0);
        }

        public IndexedHashtable connect()
        {
            int lth = cxnTable.Count;
            QueryThread[] queries = new QueryThread[lth];
            Thread[] threads = new Thread[lth];
            for (int i = 0; i < lth; i++)
            {
                queries[i] = new QueryThread(cxnTable.GetValue(i), "connect", new Object[0]);
                //queries[i].execute();
                threads[i] = new Thread(new ThreadStart(queries[i].execute));
                threads[i].Start();
            }
            IndexedHashtable result = new IndexedHashtable(lth);
            for (int i = 0; i < lth; i++)
            {
                string key = (string)cxnTable.GetKey(i);
                threads[i].Join();

                //Need to report result whether it's a connection or an exception.
                if (queries[i].isExceptionResult())
                {
                    result.Add(key,queries[i].Result);
                }
                else
                {
                    result.Add(key,((Connection)cxnTable.GetValue(key)).DataSource);
                }
            }

            //Now for all the results that failed to connect, we remove them from the connection table.
            for (int i = 0; i < lth; i++)
            {
                if (queries[i].isExceptionResult())
                {
                    string key = (string)cxnTable.GetKey(i);
                    cxnTable.Remove(key);
                    cxnTable.Add(key, queries[i].Result);
                }
            }
            return result;
        }
    	
	    public IndexedHashtable disconnect()
	    {
            int lth = cxnTable.Count;
            QueryThread[] queries = new QueryThread[lth];
            Thread[] threads = new Thread[lth];
            for (int i = 0; i < lth; i++)
            {
                queries[i] = new QueryThread(cxnTable.GetValue(i), "disconnect", new Object[0]);
                threads[i] = new Thread(new ThreadStart(queries[i].execute));
                threads[i].Start();
            }
            IndexedHashtable result = new IndexedHashtable(lth);
            for (int i = 0; i < lth; i++)
            {
                string key = (string)cxnTable.GetKey(i);
                threads[i].Join();
                if (queries[i].isExceptionResult())
                {
                    result.Add(key, queries[i].Result);
                }
                else
                {
                    result.Add(key, "OK");
                }
            }
            cxnTable.Clear();
            return result;
        }

        public IndexedHashtable disconnectRemoteSites()
        {
            int lth = cxnTable.Count - 1;
            if (lth <= 0)
            {
                return null;
            }
            QueryThread[] queries = new QueryThread[lth];
            Thread[] threads = new Thread[lth];
            for (int i = 0; i < lth; i++)
            {
                queries[i] = new QueryThread(cxnTable.GetValue(i+1), "disconnect", new Object[0]);
                threads[i] = new Thread(new ThreadStart(queries[i].execute));
                threads[i].Start();
            }
            IndexedHashtable result = new IndexedHashtable(lth);
            for (int i = 0; i < lth; i++)
            {
                string key = (string)cxnTable.GetKey(i + 1);
                threads[i].Join();
                if (queries[i].isExceptionResult())
                {
                    result.Add(key, queries[i].Result);
                }
                else
                {
                    result.Add(key, "OK");
                }
            }
            for (int i = 0; i < result.Count; i++)
            {
                cxnTable.Remove(result.GetKey(i));
            }
            return result;
        }

        public IndexedHashtable execute(String daoName, String methodName, Object[] args)
	    {
            int lth = cxnTable.Count;
		    QueryThread[] queries = new QueryThread[lth];
            Thread[] threads = new Thread[lth];
            for (int i = 0; i < lth; i++)
		    {
                if (cxnTable.GetValue(i).GetType().IsAssignableFrom(typeof(Exception)))
                {
                    continue;
                }
                Object dao = ((Connection)cxnTable.GetValue(i)).getDao(daoName);
                queries[i] = new QueryThread(dao, methodName, args);
                threads[i] = new Thread(new ThreadStart(queries[i].execute));
                threads[i].Start();
            }
            IndexedHashtable result = new IndexedHashtable(cxnTable.Count);
            for (int i=0; i<threads.Length; i++)
            {
                if (cxnTable.GetValue(i).GetType().IsAssignableFrom(typeof(Exception)))
                {
                    result.Add((string)cxnTable.GetKey(i),(Exception)cxnTable.GetValue(i));
                    continue;
                }
                try
                {
				    threads[i].Join();
                    result.Add((String)cxnTable.GetKey(i), queries[i].Result);
                }
                catch (Exception)
                {
                    //throw new MdoException(e);
                }
		    }
		    return result;
	    }

        public Dictionary<string, object> debug(String daoName, String methodName, Object[] args)
        {
            int lth = cxnTable.Count;
            QueryThread[] queries = new QueryThread[lth];
            Dictionary<string, object> result = new Dictionary<string, object>(cxnTable.Count);
            for (int i = 0; i < lth; i++)
            {
                Object dao = ((Connection)cxnTable.GetValue(i)).getDao(daoName);
                queries[i] = new QueryThread(cxnTable.GetValue(i), methodName, args);
                queries[i].execute();
                result.Add((String)cxnTable.GetKey(i), queries[i].Result);
            }
            return result;
        }

        public static IndexedHashtable execute2(IndexedHashtable cxnTable, string daoName, string methodName, Object[] args)
        {
            if (cxnTable == null || cxnTable.Count == 0)
            {
                throw new Exception("No connections!");
            }
            int lth = cxnTable.Count;
            QueryThread[] queries = new QueryThread[lth];
            Thread[] threads = new Thread[lth];
            for (int i = 0; i < lth; i++)
            {
                if (cxnTable.GetValue(i).GetType().IsAssignableFrom(typeof(Exception)))
                {
                    continue;
                }
                Connection cxn = (Connection)cxnTable.GetValue(i);
                if (!cxn.IsConnected)
                {
                    continue;
                }
                Object dao = ((Connection)cxnTable.GetValue(i)).getDao(daoName);
                if (dao == null)
                {
                    continue;
                }
                queries[i] = new QueryThread(dao, methodName, args);
                threads[i] = new Thread(new ThreadStart(queries[i].execute));
                threads[i].Start();
            }
            IndexedHashtable result = new IndexedHashtable(cxnTable.Count);
            for (int i = 0; i < threads.Length; i++)
            {
                if (cxnTable.GetValue(i).GetType().IsAssignableFrom(typeof(Exception)))
                {
                    result.Add((string)cxnTable.GetKey(i), (Exception)cxnTable.GetValue(i));
                    continue;
                }
                Connection cxn = (Connection)cxnTable.GetValue(i);
                if (!cxn.IsConnected)
                {
                    result.Add((string)cxnTable.GetKey(i), new Exception("Source is not connected"));
                    continue;
                }
                Object dao = ((Connection)cxnTable.GetValue(i)).getDao(daoName);
                if (dao == null)
                {
                    result.Add((string)cxnTable.GetKey(i), new Exception("Invalid dao: " + daoName));
                    continue;
                }
                threads[i].Join();
                result.Add((String)cxnTable.GetKey(i), queries[i].Result);
            }
            return result;
        }

    }
}
