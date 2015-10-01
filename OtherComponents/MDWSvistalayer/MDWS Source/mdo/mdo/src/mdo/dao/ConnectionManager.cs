using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.utils;
using System.Threading;

namespace gov.va.medora.mdo.dao
{
    public class ConnectionManager
    {
        IndexedHashtable cxnTbl;
        string modality;

        public ConnectionManager(string modality)
        {
            this.modality = modality;
            cxnTbl = new IndexedHashtable();
        }

        public ConnectionManager(Connection cxn)
        {
            this.modality = cxn.DataSource.Modality;
            cxnTbl = new IndexedHashtable();
            addConnection(cxn);
        }

        public ConnectionManager(DataSource src)
        {
            this.modality = src.Modality;
            DaoFactory df = DaoFactory.getDaoFactory(DaoFactory.getConstant(src.Protocol));
            Connection cxn = df.getConnection(src);
            cxnTbl = new IndexedHashtable();
            addConnection(cxn);
        }

        internal void checkModality(Connection cxn)
        {
            checkModality(cxn.DataSource.Modality);
        }

        internal void checkModality(DataSource src)
        {
            checkModality(src.Modality);
        }

        internal void checkModality(string modality)
        {
            if (modality != this.modality)
            {
                throw new Exception("Invalid modality");
            }
        }

        public void addConnection(Connection cxn)
        {
            if (cxnTbl == null)
            {
                cxnTbl = new IndexedHashtable();
                this.modality = cxn.DataSource.Modality;
            }
            checkModality(cxn);
            if (!cxnTbl.ContainsKey(cxn.DataSource.SiteId.Id))
            {
                cxnTbl.Add(cxn.SiteId.Key,cxn);
            }
        }

        public void addConnection(DataSource src)
        {
            if (src.Modality != this.modality)
            {
                throw new Exception("Invalid modality: expected " + this.modality + ", got " + src.Modality);
            }
            if (!cxnTbl.ContainsKey(src.SiteId.Key))
            {
                int protocol = DaoFactory.getConstant(src.Protocol);
                DaoFactory daoFactory = DaoFactory.getDaoFactory(protocol);
                cxnTbl.Add(src.SiteId.Key, daoFactory.getConnection(src));
            }
        }

        public void removeConnection(string siteId)
        {
            if (cxnTbl.ContainsKey(siteId))
            {
                Connection cxn = (Connection)cxnTbl.GetValue(siteId);
                if (cxn.IsConnected)
                {
                    cxn.disconnect();
                }
                cxnTbl.Remove(siteId);
            }
        }

        public void addConnections(string sitelist, SiteTable siteTbl)
        {
            string[] siteIds = StringUtils.split(sitelist, StringUtils.COMMA);
            for (int i = 0; i < siteIds.Length; i++)
            {
                Site site = siteTbl.getSite(siteIds[i]);
                if (site == null)
                {
                    throw new Exception("No such site: " + siteIds[i]);
                }
                DataSource src = site.getDataSourceByModality(this.modality);
                if (src == null)
                {
                    throw new Exception("No " + modality + " data source at site " + siteIds[i]);
                }
                int protocol = DaoFactory.getConstant(src.Protocol);
                DaoFactory daoFactory = DaoFactory.getDaoFactory(protocol);
                addConnection(daoFactory.getConnection(src));
            }
        }

        public void addConnections(DataSource[] sources)
        {
            for (int i = 0; i < sources.Length; i++)
            {
                int protocol = DaoFactory.getConstant(sources[i].Protocol);
                DaoFactory daoFactory = DaoFactory.getDaoFactory(protocol);
                addConnection(daoFactory.getConnection(sources[i]));
            }
        }

        public void addConnections(IndexedHashtable t)
        {
            for (int i = 0; i < t.Count; i++)
            {
                Connection cxn = (Connection)t.GetValue(i);
                addConnection(cxn);
            }
        }

        public IndexedHashtable connect()
        {
            int lth = cxnTbl.Count;
            QueryThread[] queries = new QueryThread[lth];
            Thread[] threads = new Thread[lth];
            for (int i = 0; i < lth; i++)
            {
                queries[i] = new QueryThread(cxnTbl.GetValue(i), "connect", new Object[0]);
                threads[i] = new Thread(new ThreadStart(queries[i].execute));
                threads[i].Start();
            }
            IndexedHashtable result = new IndexedHashtable(lth);
            for (int i = 0; i < lth; i++)
            {
                string key = (string)cxnTbl.GetKey(i);
                threads[i].Join();

                //Need to report result whether it's a connection or an exception.
                if (queries[i].isExceptionResult())
                {
                    result.Add(key, queries[i].Result);
                    Connection cxn = (Connection)cxnTbl.GetValue(i);
                    cxn.ErrorMessage = ((Exception)queries[i].Result).Message;
                    cxn.IsConnected = false;
                }
                else
                {
                    result.Add(key, ((Connection)cxnTbl.GetValue(key)).DataSource);
                }
            }
            return result;
        }

        IndexedHashtable getConnections()
        {
            IndexedHashtable result = new IndexedHashtable();
            for (int i = 0; i < cxnTbl.Count; i++)
            {
                Connection cxn = (Connection)cxnTbl.GetValue(i);
                if (cxn.IsConnected)
                {
                    result.Add(cxn.SiteId.Key, cxn);
                }
            }
            if (result.Count == 0)
            {
                return null;
            }
            return result;
        }

        public IndexedHashtable Connections
        {
            get { return getConnections(); }
        }

        public Connection getConnection(string id)
        {
            if (cxnTbl == null || !cxnTbl.ContainsKey(id))
            {
                return null;
            }
            return (Connection)cxnTbl.GetValue(id);
        }

        public Connection LoginConnection
        {
            get { return (Connection)cxnTbl.GetValue(0); }
        }

        public IndexedHashtable disconnect()
        {
            if (cxnTbl.Count == 0)
            {
                throw new Exception("No connections");
            }

            // Only disconnect from the ones that are connected
            IndexedHashtable myCxns = getConnections();

            int lth = myCxns.Count;
            QueryThread[] queries = new QueryThread[lth];
            Thread[] threads = new Thread[lth];
            for (int i = 0; i < lth; i++)
            {
                queries[i] = new QueryThread(myCxns.GetValue(i), "disconnect", new Object[0]);
                threads[i] = new Thread(new ThreadStart(queries[i].execute));
                threads[i].Start();
            }
            IndexedHashtable result = new IndexedHashtable(lth);
            for (int i = 0; i < lth; i++)
            {
                string key = (string)myCxns.GetKey(i);
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

            // We only disconnected from connected connections, but we want to clear
            // the entire table
            cxnTbl = new IndexedHashtable();   
            return result;
        }

        public IndexedHashtable disconnectRemoteSites()
        {
            if (cxnTbl.Count < 2)
            {
                throw new Exception("No remote connections");
            }

            // Only disconnect from the ones that are connected
            IndexedHashtable myCxns = getConnections();

            int lth = myCxns.Count - 1;
            QueryThread[] queries = new QueryThread[lth];
            Thread[] threads = new Thread[lth];
            for (int threadIdx = 0, cxnIdx = 1; threadIdx < lth; threadIdx++, cxnIdx++)
            {
                queries[threadIdx] = new QueryThread(myCxns.GetValue(cxnIdx), "disconnect", new Object[0]);
                threads[threadIdx] = new Thread(new ThreadStart(queries[threadIdx].execute));
                threads[threadIdx].Start();
            }
            IndexedHashtable result = new IndexedHashtable(lth);
            for (int threadIdx = 0, cxnIdx = 1; threadIdx < lth; threadIdx++, cxnIdx++)
            {
                string key = (string)myCxns.GetKey(cxnIdx);
                threads[threadIdx].Join();
                if (queries[threadIdx].isExceptionResult())
                {
                    result.Add(key, queries[threadIdx].Result);
                }
                else
                {
                    result.Add(key, "OK");
                }
            }

            // Now remove all but the logon connection from the table
            Connection loginCxn = LoginConnection;
            cxnTbl = new IndexedHashtable();
            cxnTbl.Add(loginCxn.SiteId.Key, loginCxn);
            return result;
        }

        public string Modality
        {
            get { return modality; }
        }

        public bool isAuthorized
        {
            get 
            {
                if (cxnTbl == null || cxnTbl.Count == 0)
                {
                    return false;
                }
                Connection cxn = (Connection)cxnTbl.GetValue(0);
                if (!cxn.IsConnected || cxn.Uid == "")
                {
                    return false;
                }
                return true; 
            }
        }

        public bool isAuthorizedForSite(string sitecode)
        {
            if (!isAuthorized)
            {
                return false;
            }
            Connection cxn = getConnection(sitecode);
            if (cxn == null || !cxn.IsConnected || cxn.Uid == "")
            {
                return false;
            }
            return true;
        }
    }
}
